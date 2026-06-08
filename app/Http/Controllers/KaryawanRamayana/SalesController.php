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
        $userId = Auth::id();

        // Calculate available stock per SKU, Warna, Size
        $availableStocks = SalesInput::select('sku', 'warna', 'size', 'satuan',
            DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE -qty END) as total_qty")
        )
        ->where('user_id', $userId)
        ->groupBy('sku', 'warna', 'size', 'satuan')
        ->having('total_qty', '>', 0)
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
        $userId = Auth::id();
        $insertData = [];
        $errors = [];

        foreach ($request->items as $index => $item) {
            $nominal = preg_replace('/[^0-9]/', '', $item['nominal']);
            
            // Format product_key: sku|warna|size|satuan
            $productParts = explode('|', $item['product_key']);
            $sku = $productParts[0] ?? '';
            $warna = !empty($productParts[1]) ? $productParts[1] : null;
            $size = !empty($productParts[2]) ? $productParts[2] : null;
            $satuan = !empty($productParts[3]) ? $productParts[3] : 'PSG';
            
            $qty = (int)$item['qty'];

            // Cek ketersediaan stok secara persis
            $stockInQuery = SalesInput::where('user_id', $userId)
                ->where('sku', $sku)
                ->where('type', 'stock_in');
                
            if ($warna) $stockInQuery->where('warna', $warna); else $stockInQuery->whereNull('warna');
            if ($size) $stockInQuery->where('size', $size); else $stockInQuery->whereNull('size');
            $stockIn = $stockInQuery->sum('qty');

            $stockOutQuery = SalesInput::where('user_id', $userId)
                ->where('sku', $sku)
                ->where('type', 'sale');
            if ($warna) $stockOutQuery->where('warna', $warna); else $stockOutQuery->whereNull('warna');
            if ($size) $stockOutQuery->where('size', $size); else $stockOutQuery->whereNull('size');
            $stockOut = $stockOutQuery->sum('qty');

            $availableStock = $stockIn - $stockOut;

            if ($qty > $availableStock) {
                $productName = $sku;
                if ($warna) $productName .= " ($warna)";
                if ($size) $productName .= " Size $size";
                $errors["items.$index.qty"] = "Stok '$productName' tidak mencukupi! Sisa stok: $availableStock $satuan.";
            }

            $insertData[] = [
                'user_id' => $userId,
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

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        SalesInput::insert($insertData);

        return redirect()->route('karyawan_ramayana.sales.index')->with('success', 'Data penjualan berhasil disimpan.');
    }

    public function destroy(SalesInput $sale)
    {
        if ($sale->user_id !== Auth::id() || $sale->type !== 'sale') {
            abort(403);
        }

        $sale->delete();

        return redirect()->route('karyawan_ramayana.sales.index')->with('success', 'Data penjualan berhasil dihapus.');
    }
}
