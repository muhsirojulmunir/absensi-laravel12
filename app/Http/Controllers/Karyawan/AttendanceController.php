<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Auth::user()->attendances()->latest()->get();
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return view('karyawan.attendance.index', compact('attendances', 'settings'));
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

        // Check if already checked in today
        $attendance = $user->attendances()->whereDate('date', $today)->first();

        if ($attendance) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock In hari ini.']);
        }

        $user->attendances()->create([
            'date' => $today,
            'check_in' => $now->format('H:i:s'),
            'status' => 'Hadir',
            'is_pulang_cepat' => false,
            'lat' => $request->lat,
            'long' => $request->long,
        ]);

        return response()->json(['success' => true, 'message' => 'Clock In berhasil dicatat.']);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();

        $attendance = $user->attendances()->whereDate('date', $today)->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Anda belum melakukan Clock In hari ini.']);
        }

        if ($attendance->check_out) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock Out hari ini.']);
        }

        $clockInTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $today->format('Y-m-d') . ' ' . $attendance->check_in);
        $hoursWorked = $clockInTime->diffInHours($now);
        $isPulangCepat = $hoursWorked < 8;

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'is_pulang_cepat' => $isPulangCepat,
        ]);

        return response()->json(['success' => true, 'message' => 'Clock Out berhasil dicatat.']);
    }
}
