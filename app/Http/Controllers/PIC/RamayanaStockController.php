<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
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
        
        $query = User::query()->whereHas('role', function($q) {
            $q->where('slug', 'karyawan_ramayana');
        })->with('location');
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('location', function($ql) use ($search) {
                      $ql->where('name', 'like', "%$search%");
                  });
            });
        }
        
        $users = $query->get();
        $counterStats = [];
        $totalOverallStock = 0;
        
        foreach ($users as $user) {
            $rawStocks = SalesInput::select('sku',
                DB::raw("SUM(CASE WHEN type = 'stock_in' THEN qty ELSE -qty END) as current_stock")
            )
            ->where('user_id', $user->id)
            ->where('date', '<=', $filterDate)
            ->groupBy('sku')
            ->get();
            
            $counterTotalStock = $rawStocks->sum('current_stock');
            $counterTotalSku = $rawStocks->count();
            
            $counterStats[] = [
                'user_id' => $user->id,
                'spg_name' => $user->name,
                'location' => $user->location->name ?? 'Belum Ada Lokasi',
                'total_stock' => $counterTotalStock,
                'total_sku' => $counterTotalSku
            ];
            
            $totalOverallStock += $counterTotalStock;
        }

        $locations = Location::all();

        return view('pic.ramayana-stocks.index', compact('counterStats', 'search', 'totalOverallStock', 'locations', 'filterDate'));
    }

    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);
        $search = $request->query('search', '');
        $filterDate = $request->query('date', Carbon::today()->toDateString());

        $query = SalesInput::query()->where('user_id', $user->id)
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
            
            if ($stock->type === 'stock_in') {
                $groupedStocks[$key]['has_stock_in'] = true;
            }
            
            if (empty($groupedStocks[$key]['kode_barang']) && !empty($stock->kode_barang)) {
                $groupedStocks[$key]['kode_barang'] = $stock->kode_barang;
            }
            
            if (!empty($stock->satuan) && $stock->satuan !== 'PSG') {
                $groupedStocks[$key]['satuan'] = $stock->satuan;
            }

            $qty = $stock->type === 'stock_in' ? $stock->qty : -$stock->qty;
            $groupedStocks[$key]['qty'] += $qty;
        }

        $flatStocks = [];
        
        foreach ($groupedStocks as $st) {
            if ($st['has_stock_in']) {
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


}
