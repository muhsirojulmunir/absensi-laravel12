<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\IncomingStock;

// Load SimpleXLSX directly (bundled, not from vendor/composer)
require_once app_path('Libraries/Shuchkin/SimpleXLSX.php');
use Shuchkin\SimpleXLSX;

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
        
        foreach ($users as $user) {
            $userIds = $user->location_id 
                ? User::where('location_id', $user->location_id)->pluck('id')->toArray() 
                : [$user->id];

            $rawStocks = SalesInput::select('sku',
                DB::raw("SUM(CASE WHEN type IN ('stock_in','incoming') THEN qty ELSE -qty END) as current_stock")
            )
            ->whereIn('user_id', $userIds)
            ->where('date', '<=', $filterDate)
            ->groupBy('sku')
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

        return view('pic.ramayana-stocks.index', compact('counterStats', 'search', 'totalOverallStock', 'locations', 'filterDate'));
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
            'file' => 'required|mimes:xlsx,xls,csv',
            'import_location_id' => 'required|exists:locations,id'
        ]);

        $debugLog = [];
        $debugLog[] = "=== IMPORT START (PIC): " . now()->toDateTimeString() . " ===";
        $debugLog[] = "Location ID: " . $request->import_location_id;

        if ($xlsx = SimpleXLSX::parse($request->file('file')->path())) {
            $rows = $xlsx->rows();
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

            foreach ($rows as $ri => $row) {
                foreach ($row as $ci => $cell) {
                    $val = strtolower(trim((string)$cell));
                    if (strpos($val, 'kode') !== false) {
                        $colKode = $ci;
                        $headerRowIndex = $ri;
                    }
                    if (strpos($val, 'nama') !== false) $colNama = $ci;
                    if (strpos($val, 'warna') !== false) $colWarna = $ci;
                    if (strpos($val, 'size') !== false || strpos($val, 'ukuran') !== false) $colSize = $ci;
                    if (strpos($val, 'qty') !== false || strpos($val, 'quantity') !== false) $colQty = $ci;
                }
                if ($colKode !== null && $colQty !== null) break;
            }

            if ($colKode === null) $colKode = 2;
            if ($colNama === null) $colNama = 5;
            if ($colQty === null)  $colQty  = 7;
            if ($headerRowIndex === null) $headerRowIndex = 0;

            $successCount = 0;
            $insertData = [];
            $now = now();

            $importMode = $request->input('import_mode', 'add'); // 'add' or 'replace'

            if ($importMode === 'replace') {
                SalesInput::where('user_id', $userId)
                    ->whereIn('type', ['stock_in', 'incoming'])
                    ->delete();

                IncomingStock::where('user_id', $userId)->delete();

                $existingSales = SalesInput::select('kode_barang', DB::raw("SUM(qty) as total_out"))
                    ->where('user_id', $userId)
                    ->where('type', 'sale')
                    ->whereNotNull('kode_barang')
                    ->where('kode_barang', '!=', '')
                    ->groupBy('kode_barang')
                    ->get()
                    ->keyBy('kode_barang');
            } else {
                $existingSales = collect();
            }

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $kodeBarang = isset($row[$colKode]) ? trim((string)$row[$colKode]) : '';
                $namaBarang = isset($row[$colNama]) ? trim((string)$row[$colNama]) : '';
                $qtyRaw     = isset($row[$colQty])  ? trim((string)$row[$colQty])  : '';
                $warnaExcel = ($colWarna !== null && isset($row[$colWarna])) ? trim((string)$row[$colWarna]) : '';
                $sizeExcel  = ($colSize !== null && isset($row[$colSize])) ? trim((string)$row[$colSize]) : '';

                if (empty($kodeBarang) || empty($namaBarang)) continue;
                if (!preg_match('/^\d+$/', $kodeBarang)) continue;
                if (stripos($namaBarang, 'ACCOS') !== false) continue;

                $qty = 0;
                if (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $matches)) {
                    $qty = (int)round((float)$matches[1]);
                }
                
                $satuan = 'PSG';
                if (preg_match('/[a-zA-Z]+/', $qtyRaw, $unitMatches)) {
                    $satuan = strtoupper($unitMatches[0]);
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
                    $totalOut = isset($existingSales[$kodeBarang]) ? $existingSales[$kodeBarang]->total_out : 0;
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

            $modeText = ($importMode === 'replace') ? 'Ganti Total Stok' : 'Tambah Stok (Barang Datang)';
            $message = "Berhasil ($modeText)! $successCount baris Excel dibaca, $actualInserted produk stok berhasil diproses.";
            return redirect()->route('pic_ramayana.ramayana-stocks.index')->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. ' . SimpleXLSX::parseError());
        }
    }
}
