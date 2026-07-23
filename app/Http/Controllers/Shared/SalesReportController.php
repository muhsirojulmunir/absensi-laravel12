<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesInput;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check permission if needed, but routes are protected by role middleware

        $period = $request->query('period', 'today'); // today, week, month, custom
        $locationId = $request->query('location_id');
        $userId = $request->query('user_id');
        $month = $request->query('month', date('Y-m')); // Define month always for the view

        $query = SalesInput::with(['user.location'])
            ->where('type', 'sale')
            ->whereHas('user', function ($q) {
                // only users who are karyawan_ramayana
                $q->whereHas('role', function ($r) {
                    $r->where('slug', 'karyawan_ramayana');
                });
            });

        // Date Filtering
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        } elseif ($period === 'today') {
            $query->whereDate('date', \Carbon\Carbon::today());
        } elseif ($period === 'week') {
            $query->whereBetween('date', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereYear('date', substr($month, 0, 4))
                  ->whereMonth('date', substr($month, 5, 2));
        }

        if ($locationId) {
            $query->whereHas('user', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->orderBy('date', 'desc')->get();

        $totalQty = $sales->sum('qty');
        $totalNominal = $sales->sum('nominal');

        // Get filter options
        $locations = Location::all();
        
        $users = User::whereHas('role', function ($q) {
            $q->where('slug', 'karyawan_ramayana');
        })->get();

        $routeName = $user->role->slug === 'super-admin' 
            ? 'super-admin.sales-reports.index' 
            : 'pic_ramayana.sales-reports.index';

        if ($request->ajax()) {
            $htmlTableBody = view('reports.partials.sales_table_body', compact('sales'))->render();
            $htmlTableFoot = view('reports.partials.sales_table_foot', compact('sales', 'totalQty', 'totalNominal'))->render();
            $htmlSpgSummaryTableBody = view('reports.partials.sales_spg_summary_body', compact('sales', 'userId'))->render();
            $htmlSpgSummaryTableFoot = view('reports.partials.sales_spg_summary_foot', compact('sales', 'totalQty', 'totalNominal', 'userId'))->render();

            return response()->json([
                'totalQty' => number_format($totalQty, 0, ',', '.'),
                'totalNominal' => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
                'transactionCount' => number_format($sales->count(), 0, ',', '.'),
                'htmlTableBody' => $htmlTableBody,
                'htmlTableFoot' => $htmlTableFoot,
                'htmlSpgSummaryTableBody' => $htmlSpgSummaryTableBody,
                'htmlSpgSummaryTableFoot' => $htmlSpgSummaryTableFoot,
                'hasSpgSummary' => ($sales->count() > 0 && !$userId) ? true : false,
            ]);
        }

        return view('reports.sales', compact('sales', 'totalQty', 'totalNominal', 'month', 'locationId', 'userId', 'locations', 'users', 'routeName'));
    }
}
