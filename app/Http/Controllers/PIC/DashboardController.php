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
            'total_employees' => User::withRole('karyawan')
                ->where('id', '!=', Auth::id())
                ->count(),
            'pending_requests' => LeaveRequest::whereHas('user', function ($query) {
                $query->withRole('karyawan');
            })->where('status', 'pending')->count(),
            'approved_today' => LeaveRequest::whereHas('user', function ($query) {
                $query->withRole('karyawan');
            })->where('status', 'approved')->whereDate('updated_at', now())->count(),
            'pulang_cepat_today' => \App\Models\Attendance::whereDate('date', now())
                ->where('is_pulang_cepat', true)
                ->count(),
        ];

        return view('pic.dashboard', compact('stats'));
    }
}
