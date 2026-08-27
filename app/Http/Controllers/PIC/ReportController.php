<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Show attendance report form and results.
     */
    public function index(Request $request)
    {
        $targetRoles = ['karyawan'];
        if (Auth::user()->role->slug === 'pic_ramayana') {
            $targetRoles = ['karyawan_ramayana'];
        } elseif (Auth::user()->role->slug === 'super-admin' || strtolower(Auth::user()->username) === 'superadmin1') {
            $targetRoles = ['karyawan', 'karyawan_ramayana'];
        }

        // Get list of employees (users with target roles)
        $employees = User::whereHas('role', function ($q) use ($targetRoles) {
            $q->whereIn('slug', $targetRoles);
        })->where('is_active', true)
        ->with('role')
        ->get()
        ->sortBy(function($user) {
            $roleOrder = $user->role->slug === 'karyawan' ? 1 : 2;
            return $roleOrder . '_' . strtolower($user->name);
        });

        // Determine selected employee and month (default current month)
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01');
        $end = $start->copy()->endOfMonth();

        $report = null;
        $allReports = collect();
        if ($employeeId) {
            $attendances = Attendance::query()
                ->whereRaw('user_id = ?', [$employeeId], 'and')
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->orderBy('date')
                ->get();

            $leaves = LeaveRequest::query()
                ->whereRaw('user_id = ?', [$employeeId], 'and')
                ->whereDate('start_date', '>=', $start->toDateString())
                ->whereDate('start_date', '<=', $end->toDateString())
                ->orderBy('start_date')
                ->get();

            // Simple aggregates
            $summary = [
                'total_present' => $attendances->where('status', 'Hadir')->count(),
                'total_late' => $attendances->where('status', 'Terlambat')->count(),
                'total_leave' => $leaves->count(),
                'total_sick' => $leaves->where('type', 'Sakit')->count(),
            ];

            $report = [
                'attendances' => $attendances,
                'leaves' => $leaves,
                'summary' => $summary,
                'employee' => User::whereKey($employeeId)->first(),
                'month' => $month,
            ];
        } else {
            $allReports = $employees->map(function ($employee) use ($start, $end) {
                $attendances = Attendance::query()
                    ->whereRaw('user_id = ?', [$employee->id], 'and')
                    ->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString())
                    ->orderBy('date', 'asc')
                    ->get();

                $leaves = LeaveRequest::query()
                    ->whereRaw('user_id = ?', [$employee->id], 'and')
                    ->whereDate('start_date', '>=', $start->toDateString())
                    ->whereDate('start_date', '<=', $end->toDateString())
                    ->orderBy('start_date', 'asc')
                    ->get();

                return [
                    'employee' => $employee,
                    'attendances' => $attendances,
                    'leaves' => $leaves,
                    'summary' => [
                        'total_present' => $attendances->where('status', 'Hadir')->count(),
                        'total_late' => $attendances->where('status', 'Terlambat')->count(),
                        'total_leave' => $leaves->count(),
                        'total_sick' => $leaves->where('type', 'Sakit')->count(),
                        'total_attendance_records' => $attendances->count(),
                    ],
                ];
            });
        }

        return view('pic.reports.index', compact('employees', 'report', 'allReports', 'employeeId', 'month'));
    }
}
