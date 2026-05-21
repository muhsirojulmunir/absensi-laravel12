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
        $divisionId = $request->get('division_id');
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

        $query = User::withRole('karyawan')
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
            }]);

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        $users = $query->get();
        $divisions = \App\Models\Division::all();

        // Get all holidays in this period to determine off days
        // We get holidays for the whole month/period + some buffer for weekly calculation
        $startWeek = $startDate->copy()->startOfWeek();
        $endWeek = $endDate->copy()->endOfWeek();
        $holidays = \App\Models\Holiday::whereBetween('date', [$startWeek, $endWeek])->get();

        foreach ($users as $user) {
            $user->hadir_count = $user->attendances->where('status', 'Hadir')->count();
            $user->terlambat_count = $user->attendances->where('status', 'Terlambat')->count();
            $user->pulang_cepat_count = $user->attendances->where('is_pulang_cepat', true)->count();
            
            // Collect all "Excuse" dates for the period
            $excuseDates = []; // date => type
            foreach ($user->leaveRequests as $lr) {
                $current = $lr->start_date->copy();
                while ($current->lte($lr->end_date)) {
                    $excuseDates[$current->toDateString()] = $lr->type;
                    $current->addDay();
                }
            }
            
            // Map attendances for quick lookup
            $attendanceDates = $user->attendances->pluck('status', 'date')->toArray();
            
            $user->izin_count = count(array_filter($attendanceDates, fn($s) => in_array($s, ['Izin', 'Sakit']))) + count(array_filter($excuseDates, fn($t) => in_array($t, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit', 'Lainnya'])));
            $user->libur_count = count(array_filter($attendanceDates, fn($s) => $s == 'Libur')) + count(array_filter($excuseDates, fn($t) => in_array($t, ['Libur', 'Libur (Day Off)'])));

            // Calculate Meal Allowance Weekly
            $totalAllowance = 0;
            $currentPointer = $startDate->copy()->startOfDay();
            $isLiveStreaming = str_contains(strtolower($user->division->name ?? ''), 'live streaming');
            
            while ($currentPointer->lte($endDate)) {
                $weekStart = $currentPointer->copy()->startOfWeek();
                $weekEnd = $currentPointer->copy()->endOfWeek();
                
                $segStart = $weekStart->max($startDate);
                $segEnd = $weekEnd->min($endDate);
                
                // 1. Identify Penalty for this week (Mon-Sun)
                $hasPenalty = false;
                $pPointer = $weekStart->copy();
                
                while ($pPointer->lte($weekEnd)) {
                    $dateStr = $pPointer->toDateString();
                    $status = $attendanceDates[$dateStr] ?? null;
                    $excuseType = $excuseDates[$dateStr] ?? null;
                    
                    // Check if it's a holiday
                    $isHoliday = $holidays->filter(function($h) use ($pPointer, $user) {
                        return $h->date->isSameDay($pPointer) && (is_null($h->division_id) || $h->division_id == $user->division_id);
                    })->isNotEmpty();
                    
                    $isWeekend = $pPointer->isWeekend();
                    $isStaffKantor = !$isLiveStreaming;
                    
                    $isLibur = $isHoliday || ($isStaffKantor && $isWeekend) || in_array($excuseType, ['Libur', 'Libur (Day Off)']) || $status === 'Libur';
                    
                    if (!$isLibur) {
                        // Working day penalty checks
                        if (!$status && !$excuseType) {
                            $hasPenalty = true; // Alpha
                        } elseif (in_array($excuseType, ['Izin Tidak Masuk', 'Izin Tdk Masuk', 'Sakit'])) {
                            $hasPenalty = true; // Sick or Absent Leave
                        } elseif (in_array($status, ['Izin', 'Sakit'])) {
                            $hasPenalty = true;
                        }
                    }
                    $pPointer->addDay();
                }
                
                // 2. Calculate allowance for this week segment
                $rate = $hasPenalty ? 20000 : 35000;
                $calcPointer = $segStart->copy();
                
                while ($calcPointer->lte($segEnd)) {
                    $dateStr = $calcPointer->toDateString();
                    $status = $attendanceDates[$dateStr] ?? null;
                    
                    // Allowance only given if present
                    if (in_array($status, ['Hadir', 'Terlambat'])) {
                        $totalAllowance += $rate;
                    }
                    $calcPointer->addDay();
                }
                
                $currentPointer = $weekEnd->addDay();
            }
            
            $user->total_meal_allowance = $totalAllowance;
        }

        return view('hrd.attendance.recap', [
            'users' => $users,
            'divisions' => $divisions,
            'divisionId' => $divisionId,
            'period' => $period,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'currentMonth' => (int)$startDate->format('m'),
            'currentYear' => (int)$startDate->format('Y')
        ]);
    }
}
