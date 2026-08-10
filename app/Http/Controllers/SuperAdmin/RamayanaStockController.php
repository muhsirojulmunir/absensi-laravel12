<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use App\Models\IncomingStock;
use App\Models\IncomingStockItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ExcelImportReader;

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

        return view('super-admin.ramayana-stocks.index', compact(
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
        $debugLog[] = "=== IMPORT START: " . now()->toDateTimeString() . " ===";
        $debugLog[] = "Location ID: " . $request->import_location_id;

        try {
            $ext  = strtolower($request->file('file')->getClientOriginalExtension());
            $rows = ExcelImportReader::readRows($request->file('file')->path(), $ext);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. ' . $e->getMessage());
        }

        if (true) {
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

            // ── Deteksi baris header & kolom ──────────────────────────────────────────
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

            $debugLog[] = "Detected columns: Kode=$colKode, Nama=$colNama, Warna=" . ($colWarna ?? 'null') . ", Size=" . ($colSize ?? 'null') . ", Qty=$colQty, HeaderRow=$headerRowIndex";

            $successCount = 0;
            $skippedCount = 0;
            $insertData = [];
            $now = now();

            $importMode = $request->input('import_mode', 'add'); // 'add' or 'replace'
            $debugLog[] = "Import Mode: " . $importMode;

            if ($importMode === 'replace') {
                // Hapus SEMUA stock_in DAN incoming lama milik user ini (clean slate per import)
                SalesInput::where('user_id', $userId)
                    ->whereIn('type', ['stock_in', 'incoming'])
                    ->delete();
                $debugLog[] = "Deleted all old stock_in and incoming for user $userId (Replace Mode)";

                // Hapus juga riwayat barang masuk (incoming_stocks + items cascade)
                IncomingStock::where('user_id', $userId)->delete();
                $debugLog[] = "Deleted all incoming_stocks history for user $userId (Replace Mode)";

                // PENTING: Data penjualan (sale) TIDAK dihapus — itu data krusial!
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
                $debugLog[] = "Loaded existing sales grouped by kode+size: " . $existingSales->count() . " entries";
            } else {
                $debugLog[] = "Preserving existing stocks for user $userId (Add/Tambah Mode)";
                $existingSales = collect();
            }

            // ── Parsing baris data ──────────────────────────────────────────────────────
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $kodeBarang = isset($row[$colKode]) ? trim((string)$row[$colKode]) : '';
                $namaBarang = isset($row[$colNama]) ? trim((string)$row[$colNama]) : '';
                // Qty bisa berupa angka (int/float dari PhpSpreadsheet) atau string format Ramayana
                $qtyCell    = $row[$colQty] ?? null;
                $qtyRaw     = trim((string)$qtyCell);
                $warnaExcel = ($colWarna !== null && isset($row[$colWarna])) ? trim((string)$row[$colWarna]) : '';
                $sizeExcel  = ($colSize !== null && isset($row[$colSize])) ? trim((string)$row[$colSize]) : '';

                // ── Filter baris tidak valid ──────────────────────────────────────────
                // 1. Baris kosong — nama barang wajib ada (kode boleh '-' atau kosong)
                if ($namaBarang === '') {
                    continue;
                }
                // 2. Baris footer / subtotal / total Excel Ramayana
                $lowerNama = strtolower($namaBarang);
                $lowerKode = strtolower($kodeBarang);
                if (
                    stripos($namaBarang, 'ACCOS') !== false ||
                    in_array(trim($lowerNama), ['total', 'grand total', 'subtotal', 'jumlah', 'total :', 'total:']) ||
                    in_array(trim($lowerKode), ['total', 'grand total', 'subtotal', 'jumlah'])
                ) {
                    $skippedCount++;
                    $debugLog[] = "SKIP[$i] footer/accos: kode=$kodeBarang nama=$namaBarang";
                    continue;
                }
                // 3. Nama barang terlalu pendek (< 3 karakter) = baris invalid
                if (mb_strlen($namaBarang) < 3) {
                    $skippedCount++;
                    continue;
                }

                // ── Normalisasi kode barang ───────────────────────────────────────────
                // Format Ramayana POS: kode barang seringkali '-' (tidak ada kode artikel)
                // Dalam kasus itu, gunakan nama barang sebagai identifier (kode dikosongkan)
                $isDashKode = ($kodeBarang === '-' || $kodeBarang === '' || $kodeBarang === '0');
                if ($isDashKode) {
                    $kodeBarang = ''; // simpan kosong, sku = nama barang
                } else {
                    // Tolak kode yang terlalu pendek (< 3 char tanpa separator)
                    $kodeClean = preg_replace('/[\s\-_\.]+/', '', $kodeBarang);
                    if (strlen($kodeClean) < 3) {
                        $skippedCount++;
                        $debugLog[] = "SKIP[$i] kode tidak valid: '$kodeBarang'";
                        continue;
                    }
                }

                // ── Ekstrak qty ───────────────────────────────────────────────────────
                // Format Ramayana POS: qty bisa negatif (saldo defisit) → ambil nilai absolut
                $qty = 0;
                if (is_numeric($qtyCell) && $qtyCell !== null && $qtyCell !== '') {
                    // PhpSpreadsheet mengembalikan angka langsung
                    $qty = abs((int)round((float)$qtyCell));
                } elseif (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $matches)) {
                    $qty = abs((int)round((float)$matches[1]));
                }

                $satuan = 'PSG';
                if (preg_match('/[-\d]+\.?\d*\s+([A-Za-z]+)/', $qtyRaw, $unitMatches)) {
                    $unitUp = strtoupper($unitMatches[1]);
                    // Hanya ambil unit yang masuk akal
                    if (in_array($unitUp, ['PSG', 'PCS', 'BJ', 'LSN', 'LUSIN', 'KODI', 'SET', 'BOX', 'DOS'])) {
                        $satuan = $unitUp;
                    }
                }

                // ── Ekstrak size dari nama barang atau kolom size ─────────────────────
                $size = '';
                $sku  = $namaBarang;
                if (!empty($sizeExcel)) {
                    $size = $sizeExcel;
                } else {
                    // Pola: nama diakhiri spasi + 2-3 digit (ukuran sepatu)
                    if (preg_match('/\s(\d{2,3})$/', $namaBarang, $matches)) {
                        $size = $matches[1];
                        $sku  = trim(substr($namaBarang, 0, -strlen($matches[0])));
                    }
                }

                $warna = $warnaExcel;

                if ($importMode === 'replace') {
                    // Kunci pencarian: gunakan kode jika ada, fallback ke sku
                    $lookupKey = ($kodeBarang !== '') ? $kodeBarang : $sku;
                    $saleKey  = $lookupKey . '|' . $size;
                    $totalOut = isset($existingSales[$saleKey]) ? (int)$existingSales[$saleKey]->total_out : 0;
                    $finalQty = $qty + $totalOut;
                    $insertType = 'stock_in';
                    if ($successCount <= 5) {
                        $debugLog[] = "REPLACE[$i]: kode=$kodeBarang size=$size excelQty=$qty sales=$totalOut finalQty=$finalQty";
                    }
                } else {
                    $finalQty   = $qty;
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
                if ($successCount <= 5) {
                    $debugLog[] = "OK[$i]: kode=$kodeBarang | sku=$sku | size=$size | qty=$qty | finalQty=$finalQty";
                }
            }
            $debugLog[] = "Parsed OK: $successCount | Skipped: $skippedCount";


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

            // Simpan timestamp import terakhir untuk lokasi ini
            $locationId = $request->import_location_id;
            Setting::updateOrCreate(
                ['key' => "stock_last_import_{$locationId}"],
                ['value' => now()->toDateTimeString()]
            );

            $modeText = ($importMode === 'replace') ? 'Ganti Total Stok' : 'Tambah Stok (Barang Datang)';
            $message = "Berhasil ($modeText)! $successCount baris Excel dibaca, $actualInserted produk stok berhasil diproses.";
            return redirect()->route('super-admin.ramayana-stocks.index')->with('success', $message);
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
