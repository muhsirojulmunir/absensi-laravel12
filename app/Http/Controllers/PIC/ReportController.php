<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Show attendance report form and results.
     */
    public function index(Request $request)
    {
        // Get list of employees (users with role 'karyawan')
        $employees = User::whereHas('role', function ($q) {
            $q->where('slug', 'karyawan');
        })->where('is_active', true)->orderBy('name')->get();

        // Determine selected employee and month (default current month)
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $report = null;
        if ($employeeId) {
            $start = Carbon::parse($month . '-01');
            $end = $start->copy()->endOfMonth();

            $attendances = Attendance::where('user_id', $employeeId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get();

            $leaves = LeaveRequest::where('user_id', $employeeId)
                ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('start_date')
                ->get();

            // Simple aggregates
            $summary = [
                'total_present' => $attendances->where('status', 'hadir')->count(),
                'total_late' => $attendances->where('status', 'telat')->count(),
                'total_leave' => $leaves->count(),
                'total_sick' => $leaves->where('type', 'Sakit')->count(),
            ];

            $report = [
                'attendances' => $attendances,
                'leaves' => $leaves,
                'summary' => $summary,
                'employee' => User::find($employeeId),
                'month' => $month,
            ];
        }

        return view('pic.reports.index', compact('employees', 'report', 'employeeId', 'month'));
    }
}
