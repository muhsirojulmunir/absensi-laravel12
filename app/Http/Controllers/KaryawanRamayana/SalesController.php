<?php

namespace App\Http\Controllers\KaryawanRamayana;

use App\Http\Controllers\Controller;
use App\Models\SalesInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $sales = $user->salesInputs()
            ->sale()
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalNominal = $sales->sum('nominal');
        $totalQty = $sales->sum('qty');

        return view('karyawan_ramayana.sales.index', compact('sales', 'month', 'totalNominal', 'totalQty'));
    }

    public function create()
    {
        $user = Auth::user();
        $userIds = $user->location_id 
            ? \App\Models\User::where('location_id', $user->location_id)->pluck('id')->toArray() 
            : [$user->id];

        // Calculate available stock per SKU, Warna, Size
        $availableStocks = SalesInput::select(
            'sku', 
            DB::raw("IFNULL(size, '') as size"), 
            'satuan',
            DB::raw("SUM(CASE WHEN type IN ('stock_in', 'incoming') THEN qty ELSE -qty END) as total_qty")
        )
        ->whereIn('user_id', $userIds)
        ->groupBy('sku', DB::raw("IFNULL(size, '')"), 'satuan')
        ->get();

        return view('karyawan_ramayana.sales.create', compact('availableStocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_key' => 'required|string',
            'items.*.nominal' => 'required',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $date = $request->date;
        $user = Auth::user();
        $userIds = $user->location_id 
            ? \App\Models\User::where('location_id', $user->location_id)->pluck('id')->toArray() 
            : [$user->id];
        $insertData = [];
        $errors = [];

        foreach ($request->items as $index => $item) {
            $nominal = preg_replace('/[^0-9]/', '', $item['nominal']);
            
            // Format product_key: sku|size|satuan
            $productParts = explode('|', $item['product_key']);
            $sku = $productParts[0] ?? '';
            $warna = '';
            $size = isset($productParts[1]) && $productParts[1] !== '' ? $productParts[1] : '';
            $satuan = isset($productParts[2]) && $productParts[2] !== '' ? $productParts[2] : 'PSG';
            
            $qty = (int)$item['qty'];

            // Cek ketersediaan stok secara persis di seluruh user dalam toko yang sama
            $stockInQuery = SalesInput::whereIn('user_id', $userIds)
                ->where('sku', '=', $sku, 'and')
                ->whereIn('type', ['stock_in', 'incoming']);
                
            if ($size) {
                $stockInQuery->where('size', '=', $size, 'and');
            } else {
                $stockInQuery->where(function($q) {
                    $q->whereNull('size')->orWhere('size', '=', '', 'and');
                });
            }
            $stockIn = $stockInQuery->sum('qty');

            $stockOutQuery = SalesInput::whereIn('user_id', $userIds)
                ->where('sku', '=', $sku, 'and')
                ->where('type', '=', 'sale', 'and');
            if ($size) {
                $stockOutQuery->where('size', '=', $size, 'and');
            } else {
                $stockOutQuery->where(function($q) {
                    $q->whereNull('size')->orWhere('size', '=', '', 'and');
                });
            }
            $stockOut = $stockOutQuery->sum('qty');

            $availableStock = $stockIn - $stockOut;

            $newStock = $availableStock - $qty;
            if ($newStock <= 0) {
                $productName = $sku;
                if ($size) $productName .= " Size $size";
                if ($newStock == 0) {
                    $errors[] = "Peringatan: Stok '$productName' sekarang habis (0 $satuan).";
                } else {
                    $errors[] = "Peringatan: Stok '$productName' menjadi minus ($newStock $satuan).";
                }
            }

            $insertData[] = [
                'user_id' => $user->id,
                'type' => 'sale',
                'date' => $date,
                'sku' => $sku,
                'size' => $size,
                'warna' => $warna,
                'satuan' => $satuan,
                'nominal' => $nominal,
                'qty' => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        SalesInput::insert($insertData);

        if (!empty($errors)) {
            $warningMessage = implode('<br>', $errors);
            return redirect()->route('karyawan_ramayana.sales.index')->with('success', 'Data penjualan berhasil disimpan.')->with('warning', $warningMessage);
        }

        return redirect()->route('karyawan_ramayana.sales.index')->with('success', 'Data penjualan berhasil disimpan.');
    }

    public function destroy(SalesInput $sale)
    {
        if ($sale->user_id !== Auth::id() || $sale->type !== 'sale') {
            abort(403);
        }

        SalesInput::destroy($sale->id);

        return redirect()->route('karyawan_ramayana.sales.index')->with('success', 'Data penjualan berhasil dihapus.');
    }
}
