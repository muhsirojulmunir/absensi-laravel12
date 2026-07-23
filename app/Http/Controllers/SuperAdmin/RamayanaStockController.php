<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Load SimpleXLSX directly (bundled, not from vendor/composer)
require_once app_path('Libraries/Shuchkin/SimpleXLSX.php');
use Shuchkin\SimpleXLSX;

class RamayanaStockController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $filterDate = $request->query('date', Carbon::today()->toDateString());
        
        $query = User::whereHas('role', function($q) {
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
        
        foreach ($users as $user) {
            $userIds = $user->location_id 
                ? User::where('location_id', $user->location_id)->pluck('id')->toArray() 
                : [$user->id];

            $rawStocks = SalesInput::select(DB::raw('MAX(kode_barang) as kode_barang'), 'sku', 'satuan',
                DB::raw("SUM(CASE WHEN type IN ('stock_in','incoming') THEN qty ELSE -qty END) as current_stock")
            )
            ->whereIn('user_id', $userIds)
            ->where('date', '<=', $filterDate)
            ->groupBy('sku', 'satuan')
            ->get();
            
            // Hanya jumlah stok yang positif (tersedia)
            $counterTotalStock = $rawStocks->where('current_stock', '>', 0)->sum('current_stock');
            $counterTotalSku = $rawStocks->where('current_stock', '>', 0)->count();
            
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

        return view('super-admin.ramayana-stocks.index', compact('counterStats', 'search', 'totalOverallStock', 'locations', 'filterDate'));
    }

    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);
        $search = $request->query('search', '');
        $filterDate = $request->query('date', Carbon::today()->toDateString());

        $userIds = $user->location_id 
            ? User::where('location_id', $user->location_id)->pluck('id')->toArray() 
            : [$user->id];

        $query = SalesInput::whereIn('user_id', $userIds)
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

        return view('super-admin.ramayana-stocks.show', compact(
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
            'file' => 'required|mimes:xlsx,xls,csv',
            'import_location_id' => 'required|exists:locations,id'
        ]);

        $debugLog = [];
        $debugLog[] = "=== IMPORT START: " . now()->toDateTimeString() . " ===";
        $debugLog[] = "Location ID: " . $request->import_location_id;

        if ($xlsx = SimpleXLSX::parse($request->file('file')->path())) {
            $rows = $xlsx->rows();
            $debugLog[] = "Total rows in Excel: " . count($rows);

            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel sama sekali kosong atau gagal terbaca.');
            }

            // Log first 10 rows for debugging
            foreach (array_slice($rows, 0, 10) as $i => $r) {
                $debugLog[] = "Row[$i]: " . json_encode($r);
            }

            // Temukan User (Karyawan Ramayana) yang bertugas di lokasi tersebut
            $userId = \App\Models\User::where('location_id', $request->import_location_id)
                ->whereHas('role', function($q) {
                    $q->where('slug', 'karyawan_ramayana');
                })->value('id');

            if (!$userId) {
                return redirect()->back()->with('error', 'Lokasi ini belum memiliki akun Karyawan Ramayana. Silakan tugaskan/edit akun Karyawan ke lokasi ini terlebih dahulu.');
            }
            $debugLog[] = "User ID found: $userId";

            // =====================================================
            // AUTO-DETECT KOLOM: Cari baris header untuk menentukan
            // posisi kolom Kode Barang, Nama Barang, Warna, Size, Qty
            // =====================================================
            $colKode = null;
            $colNama = null;
            $colWarna = null;
            $colSize = null;
            $colQty  = null;
            $headerRowIndex = null;

            foreach ($rows as $ri => $row) {
                foreach ($row as $ci => $cell) {
                    $val = strtolower(trim((string)$cell));
                    if (strpos($val, 'kode') !== false) {
                        $colKode = $ci;
                        $headerRowIndex = $ri;
                    }
                    if (strpos($val, 'nama') !== false) {
                        $colNama = $ci;
                    }
                    if (strpos($val, 'warna') !== false) {
                        $colWarna = $ci;
                    }
                    if (strpos($val, 'size') !== false || strpos($val, 'ukuran') !== false) {
                        $colSize = $ci;
                    }
                    if (strpos($val, 'qty') !== false || strpos($val, 'quantity') !== false) {
                        $colQty = $ci;
                    }
                }
                // Jika sudah menemukan minimal Kode dan Qty, kita asumsikan baris header sudah ketemu
                if ($colKode !== null && $colQty !== null) break;
            }

            // Fallback jika header tidak ditemukan sama sekali
            if ($colKode === null) $colKode = 2;
            if ($colNama === null) $colNama = 5;
            if ($colQty === null)  $colQty  = 7;
            if ($headerRowIndex === null) $headerRowIndex = 0;

            $debugLog[] = "Detected columns: Kode=$colKode, Nama=$colNama, Warna=$colWarna, Size=$colSize, Qty=$colQty, HeaderRow=$headerRowIndex";

            $successCount = 0;
            $insertData = [];
            $now = now();

            // Hapus SEMUA stock_in DAN incoming lama milik user ini (clean slate per import)
            SalesInput::where('user_id', $userId)
                ->whereIn('type', ['stock_in', 'incoming'])
                ->delete();
            $debugLog[] = "Deleted all old stock_in and incoming for user $userId";

            // Hapus juga riwayat barang masuk (incoming_stocks + items cascade)
            IncomingStock::where('user_id', $userId)->delete();
            $debugLog[] = "Deleted all incoming_stocks history for user $userId";

            // Ambil total penjualan yang sudah ada untuk kompensasi
            $existingSales = SalesInput::select('kode_barang', DB::raw("SUM(qty) as total_out"))
                ->where('user_id', $userId)
                ->where('type', 'sale')
                ->whereNotNull('kode_barang')
                ->where('kode_barang', '!=', '')
                ->groupBy('kode_barang')
                ->get()
                ->keyBy('kode_barang');

            // Mulai baca data dari baris setelah header
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $kodeBarang = isset($row[$colKode]) ? trim((string)$row[$colKode]) : '';
                $namaBarang = isset($row[$colNama]) ? trim((string)$row[$colNama]) : '';
                $qtyRaw     = isset($row[$colQty])  ? trim((string)$row[$colQty])  : '';
                $warnaExcel = ($colWarna !== null && isset($row[$colWarna])) ? trim((string)$row[$colWarna]) : '';
                $sizeExcel  = ($colSize !== null && isset($row[$colSize])) ? trim((string)$row[$colSize]) : '';

                // Skip baris kosong, header duplikat, footer ("ACCOS"), atau baris tanpa kode
                if (empty($kodeBarang) || empty($namaBarang)) continue;
                if (!preg_match('/^\d+$/', $kodeBarang)) continue; // Kode barang harus berupa angka murni
                if (stripos($namaBarang, 'ACCOS') !== false) continue;

                // Ekstrak qty dan satuan
                $qty = 0;
                if (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $matches)) {
                    $qty = (int)round((float)$matches[1]);
                }
                
                $satuan = 'PSG'; // Default
                if (preg_match('/[a-zA-Z]+/', $qtyRaw, $unitMatches)) {
                    $satuan = strtoupper($unitMatches[0]);
                }

                // Jika kolom size ada, gunakan kolom size, jika tidak ada fallback ke regex akhir nama
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

                // Kompensasi: stock_in = stok_excel + total_sudah_terjual
                $totalOut = isset($existingSales[$kodeBarang]) ? $existingSales[$kodeBarang]->total_out : 0;
                $requiredStockIn = $qty + $totalOut;

                $insertData[] = [
                    'user_id'     => $userId,
                    'type'        => 'stock_in',
                    'date'        => $now->toDateString(),
                    'sku'         => $sku,
                    'kode_barang' => $kodeBarang,
                    'size'        => $size,
                    'warna'       => $warna,
                    'satuan'      => $satuan,
                    'nominal'     => null,
                    'qty'         => $requiredStockIn,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];

                $successCount++;

                // Log beberapa data awal
                if ($successCount <= 5) {
                    $debugLog[] = "Data[$i]: kode=$kodeBarang | nama=$namaBarang | sku=$sku | size=$size | qty=$qty | stockIn=$requiredStockIn";
                }
            }

            $debugLog[] = "Total rows parsed: $successCount";
            $debugLog[] = "Total records to insert: " . count($insertData);

            // Insert semua ke tabel sekaligus
            $actualInserted = 0;
            if (!empty($insertData)) {
                $chunks = array_chunk($insertData, 500);
                foreach ($chunks as $chunk) {
                    SalesInput::insert($chunk);
                    $actualInserted += count($chunk);
                }
            }

            $debugLog[] = "Actually inserted: $actualInserted";
            $debugLog[] = "=== IMPORT END ===";

            // Simpan debug log ke file
            file_put_contents(storage_path('logs/import_debug.log'), implode("\n", $debugLog) . "\n\n", FILE_APPEND);

            $message = "Berhasil! $successCount baris Excel dibaca, $actualInserted produk stok berhasil dimasukkan ke database.";
            return redirect()->route('super-admin.ramayana-stocks.index')->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. ' . SimpleXLSX::parseError());
        }
    }

    // ========================================================================
    // BARANG MASUK (INCOMING STOCK)
    // ========================================================================

    /**
     * Form tambah barang masuk untuk counter tertentu
     */
    public function createIncoming($userId)
    {
        $user = User::findOrFail($userId);

        // Ambil daftar SKU yang sudah ada di stok counter ini
        $existingSkus = SalesInput::where('user_id', $user->id)
            ->where('type', 'stock_in')
            ->select('sku', 'kode_barang', 'size', 'warna', 'satuan')
            ->distinct()
            ->orderBy('sku')
            ->get();

        return view('super-admin.ramayana-stocks.incoming.create', compact('user', 'existingSkus'));
    }

    /**
     * Simpan barang masuk
     */
    public function storeIncoming(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalItems = count($request->items);
            $totalQty = 0;

            // Buat record riwayat
            $incoming = IncomingStock::create([
                'user_id' => $user->id,
                'date' => $request->date,
                'note' => $request->note,
                'total_items' => $totalItems,
                'total_qty' => 0, // akan diupdate setelah loop
                'created_by' => auth()->id(),
            ]);

            $now = now();

            foreach ($request->items as $item) {
                $qty = (int) $item['qty'];
                $totalQty += $qty;

                // Simpan detail item ke incoming_stock_items
                IncomingStockItem::create([
                    'incoming_stock_id' => $incoming->id,
                    'sku' => $item['sku'],
                    'kode_barang' => $item['kode_barang'] ?? null,
                    'size' => $item['size'] ?? null,
                    'warna' => $item['warna'] ?? null,
                    'satuan' => $item['satuan'] ?? 'PSG',
                    'qty' => $qty,
                ]);

                // Tambahkan ke sales_inputs sebagai type 'incoming'
                SalesInput::create([
                    'user_id' => $user->id,
                    'type' => 'incoming',
                    'date' => $request->date,
                    'sku' => $item['sku'],
                    'kode_barang' => $item['kode_barang'] ?? null,
                    'size' => $item['size'] ?? null,
                    'warna' => $item['warna'] ?? null,
                    'satuan' => $item['satuan'] ?? 'PSG',
                    'nominal' => null,
                    'qty' => $qty,
                ]);
            }

            $incoming->update(['total_qty' => $totalQty]);

            DB::commit();

            return redirect()
                ->route('super-admin.ramayana-stocks.show', $user->id)
                ->with('success', "Berhasil menambahkan $totalItems barang masuk dengan total $totalQty qty.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan barang masuk: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat barang masuk per counter
     */
    public function incomingHistory($userId)
    {
        $user = User::findOrFail($userId);

        $incomingStocks = IncomingStock::where('user_id', $user->id)
            ->with('createdBy')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        // Grup berdasarkan bulan
        $groupedByMonth = $incomingStocks->groupBy(function ($item) {
            return Carbon::parse($item->date)->translatedFormat('F Y');
        });

        $totalRecords = $incomingStocks->count();
        $grandTotalQty = $incomingStocks->sum('total_qty');
        $grandTotalItems = $incomingStocks->sum('total_items');

        return view('super-admin.ramayana-stocks.incoming.history', compact(
            'user', 'groupedByMonth', 'totalRecords', 'grandTotalQty', 'grandTotalItems'
        ));
    }

    /**
     * Detail barang masuk spesifik (format laporan stok)
     */
    public function incomingDetail($userId, $incomingStockId)
    {
        $user = User::findOrFail($userId);
        $incomingStock = IncomingStock::where('user_id', $user->id)
            ->with(['items', 'createdBy'])
            ->findOrFail($incomingStockId);

        return view('super-admin.ramayana-stocks.incoming.show', compact('user', 'incomingStock'));
    }

    /**
     * Hapus riwayat barang masuk beserta data stoknya
     */
    public function destroyIncoming($userId, $incomingStockId)
    {
        $user = User::findOrFail($userId);
        $incomingStock = IncomingStock::where('user_id', $user->id)->findOrFail($incomingStockId);

        DB::beginTransaction();
        try {
            // Hapus data incoming di sales_inputs yang sesuai tanggal dan item
            foreach ($incomingStock->items as $item) {
                SalesInput::where('user_id', $user->id)
                    ->where('type', 'incoming')
                    ->where('date', $incomingStock->date->toDateString())
                    ->where('sku', $item->sku)
                    ->where('qty', $item->qty)
                    ->limit(1)
                    ->delete();
            }

            // Hapus incoming_stock (items auto cascade)
            $incomingStock->delete();

            DB::commit();

            return redirect()
                ->route('super-admin.ramayana-stocks.incoming.history', $user->id)
                ->with('success', 'Riwayat barang masuk berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
