<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Setting;
use App\Models\IncomingStock;
use App\Services\ExcelImportReader;

class RamayanaStockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $filterDate = $request->query('date', Carbon::today()->toDateString());
        
        $query = User::query()->whereHas('role', function($q) {
            $q->where('slug', 'karyawan_ramayana');
        })->with('location');
        
        if (!empty($search)) {
            $matchingLocationIds = Location::query()
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                    
                    $cleanSearch = preg_replace('/[^a-zA-Z0-9]/', '', $search);
                    if (strlen($cleanSearch) >= 2 && strlen($cleanSearch) <= 4 && ctype_alpha($cleanSearch)) {
                        $pattern = implode('% ', str_split(strtoupper($cleanSearch))) . '%';
                        $pattern2 = '%' . implode('%', str_split(strtoupper($cleanSearch))) . '%';
                        $q->orWhere('name', 'like', $pattern)
                          ->orWhere('name', 'like', $pattern2);
                    }
                    
                    $words = array_filter(explode(' ', $search));
                    if (count($words) > 1) {
                        $q->orWhere(function($subQ) use ($words) {
                            foreach ($words as $w) {
                                $subQ->where('name', 'like', "%{$w}%");
                            }
                        });
                    }
                })
                ->pluck('id')
                ->toArray();

            $query->where(function($q) use ($search, $matchingLocationIds) {
                $q->where('name', 'like', "%$search%");
                if (!empty($matchingLocationIds)) {
                    $q->orWhereIn('location_id', $matchingLocationIds);
                    foreach ($matchingLocationIds as $locId) {
                        $q->orWhere('additional_location_ids', 'like', "%\"{$locId}\"%")
                          ->orWhere('additional_location_ids', 'like', "%{$locId}%");
                    }
                }
            });
        }
        
        $users = $query->get();
        $counterStats = [];
        $totalOverallStock = 0;
        $seenLocations = []; // Hindari double-counting jika 1 toko punya beberapa karyawan

        // Hitung total stok & total SKU langsung di database (server-side aggregation),
        // dikelompokkan per lokasi (atau per user jika user tidak punya lokasi).
        // Ini menghindari menarik puluhan ribu baris ke PHP yang sangat lambat di koneksi DB remote.
        $inner = DB::table('sales_inputs as si')
            ->join('users as u', 'u.id', '=', 'si.user_id')
            ->select(
                DB::raw('COALESCE(u.location_id, u.id) as loc_key'),
                'si.sku', 'si.satuan',
                DB::raw("SUM(CASE WHEN si.type IN ('stock_in','incoming') THEN si.qty ELSE -si.qty END) as stock")
            )
            ->where('si.date', '<=', $filterDate)
            ->groupBy('loc_key', 'si.sku', 'si.satuan');

        $aggByLocKey = DB::query()->fromSub($inner, 't')
            ->select('loc_key', DB::raw('SUM(stock) as total_stock'), DB::raw('COUNT(*) as total_sku'))
            ->where('stock', '>', 0)
            ->groupBy('loc_key')
            ->get()
            ->keyBy('loc_key');

        foreach ($users as $user) {
            $locKey = $user->location_id ?: $user->id;
            $agg = $aggByLocKey->get($locKey);

            $counterTotalStock = $agg->total_stock ?? 0;
            $counterTotalSku = $agg->total_sku ?? 0;

            $counterStats[] = [
                'user_id' => $user->id,
                'spg_name' => $user->name,
                'location' => $user->location->name ?? 'Belum Ada Lokasi',
                'total_stock' => $counterTotalStock,
                'total_sku' => $counterTotalSku
            ];

            // Hanya tambahkan ke total keseluruhan sekali per lokasi/toko
            $locationKey = $user->location_id ? 'loc_' . $user->location_id : 'user_' . $user->id;
            if (!isset($seenLocations[$locationKey])) {
                $seenLocations[$locationKey] = true;
                $totalOverallStock += $counterTotalStock;
            }
        }

        // Urutkan total stock yang terbesar ada di paling atas
        usort($counterStats, function ($a, $b) {
            return $b['total_stock'] <=> $a['total_stock'];
        });

        $locations = Location::all();

        // Ambil timestamp import terakhir untuk setiap lokasi
        $importTimestamps = Setting::where('key', 'like', 'stock_last_import_%')
            ->get()
            ->mapWithKeys(function ($s) {
                $locId = str_replace('stock_last_import_', '', $s->key);
                return [$locId => $s->value];
            })
            ->toArray();

        return view('pic.ramayana-stocks.index', compact(
            'counterStats', 'search', 'totalOverallStock', 'locations', 'filterDate', 'importTimestamps'
        ));
    }

    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);
        $search = $request->query('search', '');
        $filterDate = $request->query('date', Carbon::today()->toDateString());

        $userIds = $user->location_id 
            ? User::where('location_id', $user->location_id)->pluck('id')->toArray() 
            : [$user->id];

        $query = SalesInput::query()->whereIn('user_id', $userIds)
            ->where('date', '<=', $filterDate);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%$search%")
                  ->orWhere('kode_barang', 'like', "%$search%");
            });
        }

        $rawStocks = $query->get();

        $groupedStocks = [];
        $totalUniqueSkus = [];
        $totalOverallStock = 0;

        foreach ($rawStocks as $stock) {
            $key = $stock->sku . '|' . $stock->size;
            
            if (!isset($groupedStocks[$key])) {
                $groupedStocks[$key] = [
                    'kode_barang' => $stock->kode_barang,
                    'sku' => $stock->sku,
                    'size' => $stock->size,
                    'satuan' => $stock->satuan ?? 'PSG',
                    'qty' => 0,
                    'has_stock_in' => false
                ];
            }
            
            if (in_array($stock->type, ['stock_in', 'incoming'])) {
                $groupedStocks[$key]['has_stock_in'] = true;
            }
            
            if (empty($groupedStocks[$key]['kode_barang']) && !empty($stock->kode_barang)) {
                $groupedStocks[$key]['kode_barang'] = $stock->kode_barang;
            }
            
            if (!empty($stock->satuan) && $stock->satuan !== 'PSG') {
                $groupedStocks[$key]['satuan'] = $stock->satuan;
            }

            $qty = in_array($stock->type, ['stock_in', 'incoming']) ? $stock->qty : -$stock->qty;
            $groupedStocks[$key]['qty'] += $qty;
        }

        $flatStocks = [];
        
        foreach ($groupedStocks as $st) {
            if ($st['qty'] != 0) {
                $flatStocks[] = $st;
                $totalUniqueSkus[$st['sku']] = true;
                $totalOverallStock += $st['qty'];
            }
        }

        $uniqueSkuCount = count($totalUniqueSkus);
        $allProducts = array_keys($totalUniqueSkus);
        sort($allProducts);

        return view('pic.ramayana-stocks.show', compact(
            'user', 'flatStocks', 'search',
            'uniqueSkuCount', 'totalOverallStock', 'allProducts', 'filterDate'
        ));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Template_Import_Stok.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kode Barang', 'Nama Barang', 'Total Quantity', 'Keterangan']);
            fputcsv($file, ['01230631', 'SDL AJS 2430-1 30-35 A4ZY (2) 31', '1.00 PSG ( )', '']);
            fputcsv($file, ['01158400', 'JAM TANGAN', '45.00 LSN ( 45.00 BJ )', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'import_location_id' => 'required|exists:locations,id'
        ]);

        $allowedExtensions = ['xlsx', 'xls', 'ods', 'csv'];
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if ($ext === 'xlsb') {
            return redirect()->back()->with('error', 'File berformat Excel Binary Workbook (.xlsb) belum didukung. Silakan buka file tersebut di Excel, lalu "Save As" → pilih "Excel Workbook (*.xlsx)", kemudian upload ulang file .xlsx tersebut.');
        }
        if (!in_array($ext, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan file Excel (xlsx, xls, ods) atau CSV.');
        }

        $debugLog = [];
        $debugLog[] = "=== IMPORT START (PIC): " . now()->toDateTimeString() . " ===";
        $debugLog[] = "Location ID: " . $request->import_location_id;

        try {
            $rows = ExcelImportReader::readRows($request->file('file')->path());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. ' . $e->getMessage());
        }

        if (true) {
            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel sama sekali kosong atau gagal terbaca.');
            }

            $userId = User::where('location_id', $request->import_location_id)
                ->whereHas('role', function($q) {
                    $q->where('slug', 'karyawan_ramayana');
                })->value('id');

            if (!$userId) {
                return redirect()->back()->with('error', 'Lokasi ini belum memiliki akun Karyawan Ramayana.');
            }
            $colKode = null;
            $colNama = null;
            $colWarna = null;
            $colSize = null;
            $colQty  = null;
            $headerRowIndex = null;

            // Keyword yang dikenali dari export Ramayana POS / template internal
            $kodeKeywords  = ['kode', 'artikel', 'barcode', 'article', 'product code', 'item code'];
            $namaKeywords  = ['nama', 'deskripsi', 'description', 'produk', 'barang', 'item', 'keterangan barang'];
            $qtyKeywords   = ['qty', 'quantity', 'jumlah', 'stok', 'stock', 'total qty', 'total quantity', 'saldo'];
            $sizeKeywords  = ['size', 'ukuran', 'no.', 'nomor'];
            $warnaKeywords = ['warna', 'color', 'colour'];

            foreach ($rows as $ri => $row) {
                foreach ($row as $ci => $cell) {
                    $val = strtolower(trim((string)$cell));
                    if ($val === '') continue;

                    foreach ($kodeKeywords as $kw) {
                        if (strpos($val, $kw) !== false) { $colKode = $ci; $headerRowIndex = $ri; break; }
                    }
                    foreach ($namaKeywords as $kw) {
                        if (strpos($val, $kw) !== false) { $colNama = $ci; break; }
                    }
                    foreach ($qtyKeywords as $kw) {
                        if (strpos($val, $kw) !== false) { $colQty = $ci; break; }
                    }
                    foreach ($sizeKeywords as $kw) {
                        if (strpos($val, $kw) !== false) { $colSize = $ci; break; }
                    }
                    foreach ($warnaKeywords as $kw) {
                        if (strpos($val, $kw) !== false) { $colWarna = $ci; break; }
                    }
                }
                if ($colKode !== null && $colQty !== null) break;
            }

            // Fallback jika header tidak ditemukan — pakai kolom 0 dan 1 sebagai default
            if ($colKode === null) $colKode = 0;
            if ($colNama === null) $colNama = 1;
            if ($colQty === null)  $colQty  = 2;
            if ($headerRowIndex === null) $headerRowIndex = 0;

            $successCount = 0;
            $skippedCount = 0;
            $insertData = [];
            $now = now();

            $importMode = $request->input('import_mode', 'add'); // 'add' or 'replace'

            if ($importMode === 'replace') {
                SalesInput::where('user_id', $userId)
                    ->whereIn('type', ['stock_in', 'incoming'])
                    ->delete();

                IncomingStock::where('user_id', $userId)->delete();

                // PENTING: Data penjualan (sale) TIDAK dihapus — itu data krusial!
                // Kompensasi per kode_barang + size agar net stock = angka Excel.
                $existingSales = SalesInput::select(
                        'kode_barang',
                        'size',
                        DB::raw('SUM(qty) as total_out')
                    )
                    ->where('user_id', $userId)
                    ->where('type', 'sale')
                    ->whereNotNull('kode_barang')
                    ->where('kode_barang', '!=', '')
                    ->groupBy('kode_barang', 'size')
                    ->get()
                    ->keyBy(function ($row) {
                        return $row->kode_barang . '|' . ($row->size ?? '');
                    });
            } else {
                $existingSales = collect();
            }

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $kodeBarang = isset($row[$colKode]) ? trim((string)$row[$colKode]) : '';
                $namaBarang = isset($row[$colNama]) ? trim((string)$row[$colNama]) : '';
                $qtyCell    = $row[$colQty] ?? null;
                $qtyRaw     = trim((string)$qtyCell);
                $warnaExcel = ($colWarna !== null && isset($row[$colWarna])) ? trim((string)$row[$colWarna]) : '';
                $sizeExcel  = ($colSize !== null && isset($row[$colSize])) ? trim((string)$row[$colSize]) : '';

                // 1. Baris kosong — nama wajib ada (kode boleh '-')
                if ($namaBarang === '') continue;

                // 2. Baris footer / subtotal / total
                $lowerNama = strtolower($namaBarang);
                $lowerKode = strtolower($kodeBarang);
                if (
                    stripos($namaBarang, 'ACCOS') !== false ||
                    in_array(trim($lowerNama), ['total', 'grand total', 'subtotal', 'jumlah', 'total :', 'total:']) ||
                    in_array(trim($lowerKode), ['total', 'grand total', 'subtotal', 'jumlah'])
                ) {
                    $skippedCount++;
                    continue;
                }

                // 3. Nama barang terlalu pendek
                if (mb_strlen($namaBarang) < 3) {
                    $skippedCount++;
                    continue;
                }

                // Normalisasi kode barang
                // Format Ramayana POS: kode '-' = tidak ada kode, gunakan nama sebagai identifier
                $isDashKode = ($kodeBarang === '-' || $kodeBarang === '' || $kodeBarang === '0');
                if ($isDashKode) {
                    $kodeBarang = '';
                } else {
                    $kodeClean = preg_replace('/[\s\-_\.]+/', '', $kodeBarang);
                    if (strlen($kodeClean) < 3) {
                        $skippedCount++;
                        continue;
                    }
                }

                // Ekstrak qty — Ramayana POS bisa negatif (deficit), ambil absolut
                $qty = 0;
                if (is_numeric($qtyCell) && $qtyCell !== null && $qtyCell !== '') {
                    $qty = abs((int)round((float)$qtyCell));
                } elseif (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $matches)) {
                    $qty = abs((int)round((float)$matches[1]));
                }
                
                $satuan = 'PSG';
                if (preg_match('/[-\d]+\.?\d*\s+([A-Za-z]+)/', $qtyRaw, $unitMatches)) {
                    $unitUp = strtoupper($unitMatches[1]);
                    if (in_array($unitUp, ['PSG', 'PCS', 'BJ', 'LSN', 'LUSIN', 'KODI', 'SET', 'BOX', 'DOS'])) {
                        $satuan = $unitUp;
                    }
                }

                $size = '';
                $sku = $namaBarang;
                
                if (!empty($sizeExcel)) {
                    $size = $sizeExcel;
                } else {
                    if (preg_match('/\s(\d{2,3})$/', $namaBarang, $matches)) {
                        $size = $matches[1];
                        $sku = trim(substr($namaBarang, 0, -strlen($matches[0])));
                    }
                }

                $warna = $warnaExcel;

                if ($importMode === 'replace') {
                    // Kompensasi per kode_barang + size agar net stock = angka Excel
                    $lookupKey = ($kodeBarang !== '') ? $kodeBarang : $sku;
                    $saleKey = $lookupKey . '|' . $size;
                    $totalOut = isset($existingSales[$saleKey]) ? (int)$existingSales[$saleKey]->total_out : 0;
                    $finalQty = $qty + $totalOut;
                    $insertType = 'stock_in';
                } else {
                    $finalQty = $qty;
                    $insertType = 'incoming';
                }

                $insertData[] = [
                    'user_id'     => $userId,
                    'type'        => $insertType,
                    'date'        => $now->toDateString(),
                    'sku'         => $sku,
                    'kode_barang' => $kodeBarang,
                    'size'        => $size,
                    'warna'       => $warna,
                    'satuan'      => $satuan,
                    'nominal'     => null,
                    'qty'         => $finalQty,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];

                $successCount++;
            }

            $actualInserted = 0;
            if (!empty($insertData)) {
                $chunks = array_chunk($insertData, 500);
                foreach ($chunks as $chunk) {
                    SalesInput::insert($chunk);
                    $actualInserted += count($chunk);
                }
            }

            // Simpan timestamp import terakhir untuk lokasi ini
            $locationId = $request->import_location_id;
            Setting::updateOrCreate(
                ['key' => "stock_last_import_{$locationId}"],
                ['value' => now()->toDateTimeString()]
            );

            $modeText = ($importMode === 'replace') ? 'Ganti Total Stok' : 'Tambah Stok (Barang Datang)';
            $message = "Berhasil ($modeText)! $successCount baris Excel dibaca, $actualInserted produk stok berhasil diproses.";
            return redirect()->route('pic_ramayana.ramayana-stocks.index')->with('success', $message);
        }
    }
}
