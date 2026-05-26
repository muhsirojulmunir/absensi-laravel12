<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $divisionId = Auth::user()->division_id;

        $stats = [
            // total active employees in the same division (excluding self)
            'total_employees' => User::withRole('karyawan')
                ->where('is_active', true)
                ->where('division_id', $divisionId)
                ->where('id', '!=', Auth::id())
                ->count(),

            // pending leave requests from active employees in this division
            'pending_requests' => LeaveRequest::whereHas('user', function ($query) use ($divisionId) {
                $query->withRole('karyawan')
                    ->where('is_active', true)
                    ->where('division_id', $divisionId);
            })->where('status', 'pending')->count(),

            // approved today leave requests from active employees in this division
            'approved_today' => LeaveRequest::whereHas('user', function ($query) use ($divisionId) {
                $query->withRole('karyawan')
                    ->where('is_active', true)
                    ->where('division_id', $divisionId);
            })->where('status', 'approved')->whereDate('updated_at', now())->count(),

            // fast exit today (any employee)
            'pulang_cepat_today' => \App\Models\Attendance::whereDate('date', now())
                ->where('is_pulang_cepat', true)
                ->count(),
        ];

        return view('pic.dashboard', compact('stats'));
    }
}
