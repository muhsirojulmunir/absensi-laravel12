<?php

namespace App\Http\Controllers\KaryawanRamayana;

use App\Http\Controllers\Controller;
use App\Models\SalesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();
        
        // Group stock for this specific user
        $rawStocks = SalesInput::select('sku', 'kode_barang', 'warna', 'size', 'satuan',
                DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN type = 'sale' THEN qty ELSE 0 END) as total_out"),
                DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE -qty END) as current_stock")
            )
            ->where('user_id', $userId)
            ->groupBy('sku', 'kode_barang', 'warna', 'size', 'satuan')
            ->get();

        $groupedStocks = [];
        $totalOverallStock = 0;

        foreach ($rawStocks as $stock) {
            $key = $stock->sku . '|' . $stock->warna;

            if (!isset($groupedStocks[$key])) {
                $groupedStocks[$key] = [
                    'sku' => $stock->sku,
                    'kode_barang' => $stock->kode_barang,
                    'warna' => $stock->warna,
                    'total_stock' => 0,
                    'sizes' => []
                ];
            }

            $groupedStocks[$key]['sizes'][$stock->size] = [
                'qty' => $stock->current_stock,
                'kode' => $stock->kode_barang,
                'satuan' => $stock->satuan ?? 'PSG'
            ];
            $groupedStocks[$key]['total_stock'] += $stock->current_stock;
            $totalOverallStock += $stock->current_stock;
        }

        // Sort sizes logically
        foreach ($groupedStocks as &$group) {
            ksort($group['sizes'], SORT_NUMERIC);
        }
        unset($group);

        return view('karyawan_ramayana.stocks.index', compact('groupedStocks', 'totalOverallStock', 'user'));
    }

    public function create()
    {
        return view('karyawan_ramayana.stocks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'sku' => 'required|string|max:255',
            'warna' => 'nullable|string|max:100',
            'sizes' => 'required|array',
            'sizes.*' => 'nullable|integer|min:0',
        ]);

        $date = $request->date;
        $userId = Auth::id();
        $sku = ucwords(strtolower(trim($request->sku)));
        $warna = !empty($request->warna) ? ucwords(strtolower(trim($request->warna))) : null;

        $insertData = [];

        foreach ($request->sizes as $size => $qty) {
            if ($qty !== null && $qty !== '') {
                $insertData[] = [
                    'user_id' => $userId,
                    'type' => 'stock_in',
                    'date' => $date,
                    'sku' => $sku,
                    'size' => $size,
                    'warna' => $warna,
                    'nominal' => null,
                    'qty' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (count($insertData) > 0) {
            SalesInput::insert($insertData);
        }

        return redirect()->route('karyawan_ramayana.stocks.index')->with('success', 'Stok berhasil ditambahkan.');
    }

    public function destroy(SalesInput $stock)
    {
        if ($stock->user_id !== Auth::id() || $stock->type !== 'stock_in') {
            abort(403);
        }

        $stock->delete();

        return redirect()->route('karyawan_ramayana.stocks.index')->with('success', 'Data stok berhasil dihapus.');
    }

    public function editCatalog(Request $request)
    {
        $userId = Auth::id();
        $sku = ucwords(strtolower(trim($request->query('sku'))));
        $warna = !empty($request->query('warna')) ? ucwords(strtolower(trim($request->query('warna')))) : null;

        if (!$sku) abort(404);

        // Fetch current stock grouped by size
        $rawStocks = SalesInput::select('size',
                DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN type = 'sale' THEN qty ELSE 0 END) as total_out"),
                DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE -qty END) as current_stock")
            )
            ->where('user_id', $userId)
            ->where('sku', $sku)
            ->where('warna', $warna)
            ->groupBy('size')
            ->get();

        $sizes = [];
        foreach ($rawStocks as $stock) {
            $sizes[$stock->size] = $stock->current_stock;
        }
        ksort($sizes, SORT_NUMERIC);

        return view('karyawan_ramayana.stocks.edit', compact('sku', 'warna', 'sizes'));
    }

    public function updateCatalog(Request $request)
    {
        $request->validate([
            'sku' => 'required|string',
            'warna' => 'nullable|string',
            'sizes' => 'required|array'
        ]);

        $userId = Auth::id();
        $sku = ucwords(strtolower(trim($request->sku)));
        $warna = !empty($request->warna) ? ucwords(strtolower(trim($request->warna))) : null;

        // Fetch total_out (sales) to know how much stock_in is needed for the new current_stock
        $sales = SalesInput::select('size', DB::raw("SUM(qty) as total_out"))
            ->where('user_id', $userId)
            ->where('sku', $sku)
            ->where('warna', $warna)
            ->where('type', 'sale')
            ->groupBy('size')
            ->get()
            ->keyBy('size');

        // Delete existing stock_in for this SKU/Warna to rebuild it cleanly
        SalesInput::where('user_id', $userId)
            ->where('sku', $sku)
            ->where('warna', $warna)
            ->where('type', 'stock_in')
            ->delete();

        $insertData = [];
        $now = now();

        foreach ($request->sizes as $size => $newCurrentStock) {
            if ($newCurrentStock === null || $newCurrentStock === '') continue;

            $totalOut = isset($sales[$size]) ? $sales[$size]->total_out : 0;
            // new_stock_in = new_current_stock + total_out
            $requiredStockIn = $newCurrentStock + $totalOut;

            if ($requiredStockIn >= 0) {
                $insertData[] = [
                    'user_id' => $userId,
                    'type' => 'stock_in',
                    'date' => $now->toDateString(),
                    'sku' => $sku,
                    'size' => $size,
                    'warna' => $warna,
                    'nominal' => null,
                    'qty' => $requiredStockIn,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($insertData)) {
            SalesInput::insert($insertData);
        }

        return redirect()->route('karyawan_ramayana.stocks.index')->with('success', "Stok untuk $sku berhasil diperbarui.");
    }

    public function deleteCatalog(Request $request)
    {
        $userId = Auth::id();
        $sku = ucwords(strtolower(trim($request->query('sku'))));
        $warna = !empty($request->query('warna')) ? ucwords(strtolower(trim($request->query('warna')))) : null;

        SalesInput::where('user_id', $userId)
            ->where('sku', $sku)
            ->where('warna', $warna)
            ->delete();

        return redirect()->route('karyawan_ramayana.stocks.index')->with('success', "Semua stok untuk $sku berhasil dihapus.");
    }
}
