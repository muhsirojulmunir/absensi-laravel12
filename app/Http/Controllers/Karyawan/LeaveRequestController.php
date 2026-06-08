<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->leaveRequests()->latest();

        // Custom Date Filtering
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $leaveRequests = $query->paginate(5)->appends($request->all());

        // Monthly Stats Reset
        $statsQuery = $user->leaveRequests()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();

        return view('karyawan.leave-requests.index', compact(
            'leaveRequests', 
            'totalCount', 
            'pendingCount', 
            'approvedCount', 
            'rejectedCount'
        ));
    }

    public function store(Request $request)
    {
        $isLupaAbsen = $request->type === 'Lupa Absen';
        $isAbsenDiluar = $request->type === 'Absen Diluar';

        $request->validate([
            'type' => 'required|in:Sakit,Izin Tidak Masuk,Izin Masuk Siang,Libur,Lupa Absen,Absen Diluar',
            'sub_type' => 'nullable|required_if:type,Lupa Absen|required_if:type,Absen Diluar|in:Absen Masuk,Absen Pulang',
            // Lupa Absen: boleh tanggal lampau dalam bulan ini.
            // Absen Diluar: hari ini ke depan.
            // Lainnya: hari ini ke depan.
            'start_date' => $isLupaAbsen
                ? 'required|date|after_or_equal:' . now()->startOfMonth()->toDateString() . '|before_or_equal:today'
                : 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'time_start' => 'nullable|required_if:type,Izin Masuk Siang|required_if:type,Lupa Absen|required_if:type,Absen Diluar',
            'time_end' => 'nullable|required_if:type,Izin Masuk Siang',
            'reason' => 'required|string|max:500',
        ]);

        // ── Cek batasan 1 kali sebulan untuk Lupa Absen (Masuk/Pulang) ─────
        // Rule: dalam 1 bulan kalender, user hanya boleh 1x mengajukan
        // Lupa Absen (baik Absen Masuk maupun Absen Pulang).
        // Rejected tidak dihitung, jadi user tetap bisa mengajukan ulang.
        if ($request->type === 'Lupa Absen') {
            $startDate = Carbon::parse($request->start_date);

            $alreadyExistsThisMonth = Auth::user()->leaveRequests()
                ->where('type', 'Lupa Absen')
                ->whereIn('status', ['pending', 'approved'])          // rejected tidak dihitung
                ->whereMonth('start_date', $startDate->month)         // ← fix: pakai start_date
                ->whereYear('start_date', $startDate->year)           // ← fix: pakai start_date
                ->exists();

            if ($alreadyExistsThisMonth) {
                $bulan = $startDate->locale('id')->isoFormat('MMMM Y');
                return redirect()->back()->withInput()->withErrors([
                    'type' => "Anda sudah menggunakan jatah Lupa Absen (Absen Masuk/Pulang) untuk bulan {$bulan}. Kesempatan hanya 1 kali per bulan dan akan reset di bulan berikutnya.",
                ]);
            }
        }
        // ────────────────────────────────────────────────────────────────────

        Auth::user()->leaveRequests()->create([
            'type' => $request->type,
            'sub_type' => $request->sub_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'time_start' => $request->time_start,
            'time_end' => $request->time_end,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('karyawan.leave-requests.index')->with('success', 'Pengajuan berhasil dibuat.');
    }
}
