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
        if ($period === 'today') {
            $query->whereDate('date', \Carbon\Carbon::today());
        } elseif ($period === 'week') {
            $query->whereBetween('date', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereYear('date', substr($month, 0, 4))
                  ->whereMonth('date', substr($month, 5, 2));
        } elseif ($period === 'custom') {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }
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

        return view('reports.sales', compact('sales', 'totalQty', 'totalNominal', 'month', 'locationId', 'userId', 'locations', 'users', 'routeName'));
    }
}
