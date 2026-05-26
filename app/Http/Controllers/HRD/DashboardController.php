<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Basic Stats
        $totalEmployees = User::withRole('karyawan')->where('is_active', true)->count();
        $todayAttendance = Attendance::whereDate('date', Carbon::today())
            ->whereIn('status', ['Hadir', 'Terlambat'])
            ->count();
            
        $attendancePercentage = $totalEmployees > 0 
            ? round(($todayAttendance / $totalEmployees) * 100) 
            : 0;

        // 2. Weekly Chart Data (Last 7 Days)
        $chartData = [];
        $labels = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Attendance::whereDate('date', $date)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->count();
                
            $labels[] = $date->translatedFormat('D');
            $counts[] = $count;
        }

        return view('hrd.dashboard', compact(
            'totalEmployees',
            'todayAttendance',
            'attendancePercentage',
            'labels',
            'counts'
        ));
    }
}
