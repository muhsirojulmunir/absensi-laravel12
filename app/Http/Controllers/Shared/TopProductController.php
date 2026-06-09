<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TopProductController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'month');
        $locationId = $request->query('location_id');
        
        $query = SalesInput::with(['user.location'])
            ->where('type', 'sale')
            ->whereHas('user', function ($q) {
                $q->whereHas('role', function ($r) {
                    $r->where('slug', 'karyawan_ramayana');
                });
            });

        // Date Filtering Logic & Previous Period Dates
        $startDateCurrent = null;
        $endDateCurrent = null;
        $startDatePrevious = null;
        $endDatePrevious = null;

        if ($period === 'today') {
            $startDateCurrent = Carbon::today();
            $endDateCurrent = Carbon::today();
            $startDatePrevious = Carbon::yesterday();
            $endDatePrevious = Carbon::yesterday();
        } elseif ($period === 'week') {
            $startDateCurrent = Carbon::now()->startOfWeek();
            $endDateCurrent = Carbon::now()->endOfWeek();
            $startDatePrevious = Carbon::now()->subWeek()->startOfWeek();
            $endDatePrevious = Carbon::now()->subWeek()->endOfWeek();
        } elseif ($period === 'month') {
            $startDateCurrent = Carbon::now()->startOfMonth();
            $endDateCurrent = Carbon::now()->endOfMonth();
            $startDatePrevious = Carbon::now()->subMonth()->startOfMonth();
            $endDatePrevious = Carbon::now()->subMonth()->endOfMonth();
        } elseif ($period === 'year') {
            $startDateCurrent = Carbon::now()->startOfYear();
            $endDateCurrent = Carbon::now()->endOfYear();
            $startDatePrevious = Carbon::now()->subYear()->startOfYear();
            $endDatePrevious = Carbon::now()->subYear()->endOfYear();
        } elseif ($period === 'custom') {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            if ($startDate && $endDate) {
                $startDateCurrent = Carbon::parse($startDate);
                $endDateCurrent = Carbon::parse($endDate);
                $startDatePrevious = Carbon::parse($startDate)->subMonth();
                $endDatePrevious = Carbon::parse($endDate)->subMonth();
            }
        }

        if ($startDateCurrent && $endDateCurrent) {
            $query->whereBetween('date', [$startDateCurrent->format('Y-m-d'), $endDateCurrent->format('Y-m-d')]);
        }

        // Apply Location Filter to the base query if a specific location is selected
        if ($locationId) {
            $query->whereHas('user', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        // Clone query for different aggregations
        $globalQuery = clone $query;
        $counterQuery = clone $query;

        // 1. Global Top Products (Group by SKU, Warna, Size)
        $globalSales = $globalQuery->select('sku', 'warna', 'size', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(nominal) as total_nominal'))
            ->groupBy('sku', 'warna', 'size')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        // Calculate Trends for Global Sales
        if ($startDatePrevious && $endDatePrevious && $globalSales->count() > 0) {
            $skuWarnaPairs = $globalSales->map(function ($item) {
                return ['sku' => $item->sku, 'warna' => $item->warna];
            });

            $prevQuery = SalesInput::where('type', 'sale')
                ->whereHas('user', function ($q) {
                    $q->whereHas('role', function ($r) {
                        $r->where('slug', 'karyawan_ramayana');
                    });
                })
                ->whereBetween('date', [$startDatePrevious->format('Y-m-d'), $endDatePrevious->format('Y-m-d')]);

            if ($locationId) {
                $prevQuery->whereHas('user', function ($q) use ($locationId) {
                    $q->where('location_id', $locationId);
                });
            }

            $prevSales = $prevQuery->select('sku', 'warna', DB::raw('SUM(qty) as prev_qty'))
                ->where(function($q) use ($skuWarnaPairs) {
                    foreach ($skuWarnaPairs as $pair) {
                        $q->orWhere(function($sq) use ($pair) {
                            $sq->where('sku', $pair['sku']);
                            if ($pair['warna']) {
                                $sq->where('warna', $pair['warna']);
                            } else {
                                $sq->whereNull('warna')->orWhere('warna', '');
                            }
                        });
                    }
                })
                ->groupBy('sku', 'warna')
                ->get()
                ->keyBy(function ($item) {
                    return $item->sku . '_' . $item->warna;
                });

            foreach ($globalSales as $item) {
                $key = $item->sku . '_' . $item->warna;
                $prevQty = isset($prevSales[$key]) ? $prevSales[$key]->prev_qty : 0;
                
                if ($prevQty > 0) {
                    $trend = (($item->total_qty - $prevQty) / $prevQty) * 100;
                    $item->trend = round($trend, 1);
                } else {
                    $item->trend = $item->total_qty > 0 ? 100 : 0;
                }
                $item->prev_qty = $prevQty;
            }
        } else {
            foreach ($globalSales as $item) {
                $item->trend = 0;
                $item->prev_qty = 0;
            }
        }

        // 2. Top Products per Counter
        // We will fetch all sales grouped by location, sku, and warna
        // Wait, MySQL mode might be strict, so we must group by user's location_id as well
        // We'll join with users table to group by location_id
        $counterSalesRaw = $counterQuery->join('users', 'sales_inputs.user_id', '=', 'users.id')
            ->join('locations', 'users.location_id', '=', 'locations.id')
            ->select(
                'locations.id as location_id', 
                'locations.name as location_name', 
                'sales_inputs.sku', 
                'sales_inputs.warna', 
                DB::raw('SUM(sales_inputs.qty) as total_qty'),
                DB::raw('SUM(sales_inputs.nominal) as total_nominal')
            )
            ->groupBy('locations.id', 'locations.name', 'sales_inputs.sku', 'sales_inputs.warna')
            ->orderBy('locations.name', 'asc')
            ->orderBy('total_qty', 'desc')
            ->get();

        // Group by location in PHP to easily limit top 5 per counter
        $topPerCounter = [];
        foreach ($counterSalesRaw as $sale) {
            $locId = $sale->location_id;
            if (!isset($topPerCounter[$locId])) {
                $topPerCounter[$locId] = [
                    'location_name' => $sale->location_name,
                    'products' => []
                ];
            }
            
            // Limit to top 5 products per counter
            if (count($topPerCounter[$locId]['products']) < 5) {
                $topPerCounter[$locId]['products'][] = $sale;
            }
        }

        $locations = Location::orderBy('name')->get();

        // Determine user role for routing
        $user = Auth::user();
        $routeName = $user->role->slug === 'super-admin' 
            ? 'super-admin.top-products.index' 
            : 'pic_ramayana.top-products.index';

        return view('reports.top-products', compact(
            'globalSales', 
            'topPerCounter', 
            'period', 
            'locationId', 
            'locations',
            'routeName'
        ));
    }
}
