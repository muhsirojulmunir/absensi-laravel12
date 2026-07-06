<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private function hasActiveLupaAbsenRequestOnDate($user, $date): bool
    {
        return $user->leaveRequests()
            ->where('type', 'Lupa Absen')
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', $date)
            ->exists();
    }

    public function index()
    {
        $userAttendances = Auth::user()->attendances()->get();
        
        $holidays = \App\Models\Holiday::query()->where(function ($query) {
            $query->whereNull('division_id')
                  ->orWhere('division_id', Auth::user()->division_id);
        })->get();

        $mergedAttendances = collect();
        $attendanceDates = [];

        foreach ($userAttendances as $att) {
            $mergedAttendances->push($att);
            $attendanceDates[] = \Carbon\Carbon::parse($att->date)->format('Y-m-d');
        }

        foreach ($holidays as $holiday) {
            $holidayDate = \Carbon\Carbon::parse($holiday->date)->format('Y-m-d');
            if (!in_array($holidayDate, $attendanceDates) && $holidayDate <= \Carbon\Carbon::today()->format('Y-m-d')) {
                $mergedAttendances->push((object)[
                    'date' => $holidayDate,
                    'check_in' => null,
                    'check_out' => null,
                    'status' => 'Libur',
                    'is_pulang_cepat' => false,
                    'note' => $holiday->description
                ]);
            }
        }

        $attendances = $mergedAttendances->sortByDesc(function($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
        })->values();

        $currentMonthStats = $attendances->filter(function($item) {
            return \Carbon\Carbon::parse($item->date)->isCurrentMonth();
        });

        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return view('karyawan.attendance.index', compact('attendances', 'currentMonthStats', 'settings'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Basic validation
        $request->validate([
            'lat' => 'required|numeric',
            'long' => 'required|numeric'
        ], [
            'lat.required' => 'Lokasi latitude diperlukan.',
            'long.required' => 'Lokasi longitude diperlukan.',
        ]);

        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();
        $userDivision = $user->division ? strtolower(trim($user->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        if ($isLiveStreaming) {
            $overnightShift = $user->attendances()
                ->whereDate('date', \Carbon\Carbon::yesterday())
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->first();

            // Hanya blokir clock in jika belum lewat jam 08:30 pagi
            if ($overnightShift && $now->format('H:i') <= '08:30') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda masih memiliki shift kemarin yang belum Clock Out. Silakan Clock Out terlebih dahulu.'
                ]);
            }
        }

        // Check if already checked in today
        $attendance = $user->attendances()->whereDate('date', $today)->first();

        if ($attendance && $attendance->check_in) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock In hari ini.']);
        }

        if ($attendance && !$attendance->check_in) {
            $attendance->update([
                'check_in' => $now->format('H:i:s'),
                'status' => 'Hadir',
                'is_pulang_cepat' => false,
                'lat' => $request->lat,
                'long' => $request->long,
            ]);
        } else {
            $user->attendances()->create([
                'date' => $today,
                'check_in' => $now->format('H:i:s'),
                'status' => 'Hadir',
                'is_pulang_cepat' => false,
                'lat' => $request->lat,
                'long' => $request->long,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Clock In berhasil dicatat.']);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();

        $userDivision = $user->division ? strtolower(trim($user->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        $attendance = $user->attendances()->whereDate('date', $today)->first();

        // Khusus Live Streaming: Cek shift malam kemarin (Batas jam 08:30 pagi)
        if ($isLiveStreaming && (!$attendance || !$attendance->check_in)) {
            if ($now->format('H:i') <= '08:30') {
                $overnightAttendance = $user->attendances()
                    ->whereDate('date', \Carbon\Carbon::yesterday())
                    ->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->first();
                
                if ($overnightAttendance) {
                    $attendance = $overnightAttendance;
                }
            }
        }

        if ($attendance && $attendance->check_out) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock Out.']);
        }

        $isPulangCepat = false;

        if ($user->role->slug === 'karyawan_ramayana') {
            if ($attendance && $attendance->check_in) {
                $dateStr = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : explode(' ', (string)$attendance->date)[0];
                $clockInTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                $minutesWorked = $clockInTime->diffInMinutes($now);
                $isPulangCepat = $minutesWorked < (7 * 60);
            } else {
                $isPulangCepat = false;
            }
        } elseif (str_contains($userDivision, 'gudang')) {
            $isPulangCepat = $now->format('H:i') < '18:00';
        } else {
            if ($attendance && $attendance->check_in) {
                $dateStr = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : explode(' ', (string)$attendance->date)[0];
                $clockInTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                $minutesWorked = $clockInTime->diffInMinutes($now);
                $isPulangCepat = $minutesWorked < (8 * 60);
            } else {
                $isPulangCepat = $now->format('H:i') < '17:00'; // Default behavior if check_in is missing
            }
        }

        if ($attendance) {
            $attendance->update([
                'check_out' => $now->format('H:i:s'),
                'is_pulang_cepat' => $isPulangCepat,
                'status' => 'Hadir',
            ]);
        } else {
            $user->attendances()->create([
                'date' => $today,
                'check_in' => null,
                'check_out' => $now->format('H:i:s'),
                'status' => 'Hadir',
                'is_pulang_cepat' => $isPulangCepat,
                'lat' => $request->lat ?? 0,
                'long' => $request->long ?? 0,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Clock Out berhasil dicatat.']);
    }
}
