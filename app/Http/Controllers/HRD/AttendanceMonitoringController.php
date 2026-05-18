<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $attendances = Attendance::with(['user.division'])
            ->whereDate('date', $date)
            ->latest()
            ->get();

        return view('hrd.attendance.index', compact('attendances', 'date'));
    }

    public function recap(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($period == 'day') {
            $startDate = Carbon::parse($request->get('date', Carbon::today()->toDateString()))->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
        } elseif ($period == 'week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($period == 'month') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($period == 'custom') {
            $startDate = Carbon::parse($request->get('start_date', Carbon::today()->toDateString()))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date', Carbon::today()->toDateString()))->endOfDay();
        }

        $users = User::withRole('karyawan')
            ->with(['division', 'attendances' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }, 'leaveRequests' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'approved')
                  ->where(function($query) use ($startDate, $endDate) {
                      $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function($sub) use ($startDate, $endDate) {
                                $sub->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                  });
            }])
            ->get();

        foreach ($users as $user) {
            $user->hadir_count = $user->attendances->where('status', 'Hadir')->count();
            $user->terlambat_count = $user->attendances->where('status', 'Terlambat')->count();
            $user->pulang_cepat_count = $user->attendances->where('is_pulang_cepat', true)->count();
            
            // Collect all "Excuse" dates for the period
            $excuseDates = []; // date => type
            foreach ($user->leaveRequests as $lr) {
                $current = $lr->start_date->copy();
                while ($current->lte($lr->end_date)) {
                    if ($current->between($startDate, $endDate)) {
                        $excuseDates[$current->toDateString()] = $lr->type;
                    }
                    $current->addDay();
                }
            }
            
            // Map attendances for quick lookup
            $attendanceDates = $user->attendances->pluck('status', 'date')->toArray();
            $user->izin_count = count(array_filter($attendanceDates, fn($s) => in_array($s, ['Izin', 'Sakit']))) + count(array_filter($excuseDates, fn($t) => in_array($t, ['Izin Penting', 'Sakit', 'Lainnya'])));
            $user->libur_count = count(array_filter($attendanceDates, fn($s) => $s == 'Libur')) + count(array_filter($excuseDates, fn($t) => $t == 'Libur'));

            // Calculate Meal Allowance Weekly
            $totalAllowance = 0;
            $currentPointer = $startDate->copy()->startOfDay();
            
            while ($currentPointer->lte($endDate)) {
                $weekStart = $currentPointer->copy()->startOfWeek();
                $weekEnd = $currentPointer->copy()->endOfWeek();
                
                // Segment of the week that falls within our range
                $segStart = $weekStart->max($startDate);
                $segEnd = $weekEnd->min($endDate);
                
                // 1. Identify Penalty for this week segment
                $hasPenalty = false;
                $pPointer = $segStart->copy();
                while ($pPointer->lte($segEnd)) {
                    $dateStr = $pPointer->toDateString();
                    $status = $attendanceDates[$dateStr] ?? null;
                    $excuseType = $excuseDates[$dateStr] ?? null;
                    
                    // Alpha = No attendance record AND no approved leave request
                    $isAlpha = (!$status && !$excuseType);
                    $isIzinSakit = (in_array($status, ['Izin', 'Sakit']) || in_array($excuseType, ['Izin Penting', 'Sakit', 'Lainnya']));
                    
                    if ($isAlpha || $isIzinSakit) {
                        $hasPenalty = true;
                        break;
                    }
                    $pPointer->addDay();
                }
                
                // 2. Calculate allowance for this week segment
                $calcPointer = $segStart->copy();
                $isLiveStreaming = ($user->division->name ?? '') === 'Live Streaming';
                $rate = ($isLiveStreaming || !$hasPenalty) ? 35000 : 20000;
                
                while ($calcPointer->lte($segEnd)) {
                    $dateStr = $calcPointer->toDateString();
                    $status = $attendanceDates[$dateStr] ?? null;
                    
                    // Allowance only for Hadir/Terlambat
                    if (in_array($status, ['Hadir', 'Terlambat'])) {
                        $totalAllowance += $rate;
                    }
                    $calcPointer->addDay();
                }
                
                // Move pointer to next week
                $currentPointer = $weekEnd->addDay();
            }
            
            $user->total_meal_allowance = $totalAllowance;
        }

        return view('hrd.attendance.recap', [
            'users' => $users,
            'period' => $period,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'currentMonth' => (int)$startDate->format('m'),
            'currentYear' => (int)$startDate->format('Y')
        ]);
    }
}
