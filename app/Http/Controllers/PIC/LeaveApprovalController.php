<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Ko Henry (Rekap Izin)
        $isKoHenry = $user->role->slug === 'super-admin' && $user->id !== 1;
        // JMN (Persetujuan Izin dengan full akses hapus)
        $isJMN = $user->role->slug === 'super-admin' && $user->id === 1;

        $defaultStatus = $isKoHenry ? 'all' : 'pending';
        
        $status = $request->input('status', $defaultStatus);
        $dateFilter = $request->input('date_filter', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $targetRole = $user->role->slug === 'pic_ramayana' ? 'karyawan_ramayana' : 'karyawan';

        $query = LeaveRequest::with(['user', 'user.division'])
            ->whereHas('user', function ($q) use ($isKoHenry, $isJMN, $targetRole) {
                $q->where('is_active', true);
                if (!$isKoHenry && !$isJMN) {
                    $q->whereHas('role', fn($r) => $r->where('slug', $targetRole));
                }
            });

        // Filter Status (All, Pending, Approved, Rejected)
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter Date based on start_date
        if ($dateFilter === 'today') {
            $query->whereDate('start_date', now()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('start_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('start_date', now()->month)
                  ->whereYear('start_date', now()->year);
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        $leaveRequests = $query->latest()->paginate(10)->withQueryString();

        // Calculate monthly stats globally
        $statsQuery = LeaveRequest::whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year);

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $statsQuery)->where('status', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();

        if ($isKoHenry) {
            return view('super-admin.leave-approvals.index', compact(
                'leaveRequests', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'
            ));
        }

        return view('pic.leave-approvals.index', compact(
            'leaveRequests', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'
        ));
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => Auth::id()
        ]);

        // ── AUTO-FILL ABSENSI untuk pengajuan "Lupa Absen" & "Absen Diluar" ──
        if (in_array($leaveRequest->type, ['Lupa Absen', 'Absen Diluar']) && $leaveRequest->time_start) {
            $date    = Carbon::parse($leaveRequest->start_date)->toDateString();
            $userId  = $leaveRequest->user_id;
            $subType = $leaveRequest->sub_type; // 'Absen Masuk' atau 'Absen Pulang'
            $time    = $leaveRequest->time_start; // format H:i:s
            $isAbsenDiluar = $leaveRequest->type === 'Absen Diluar';
            $note    = $isAbsenDiluar ? 'Absen Diluar' : null;

            // Cari absensi karyawan pada tanggal tersebut
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('date', $date)
                ->first();

            if ($subType === 'Absen Masuk') {
                if ($attendance) {
                    // Sudah ada data (misal hanya check_out), isi check_in-nya
                    $updateData = [
                        'check_in' => $time,
                        'status'   => 'Hadir',
                    ];
                    if ($note) $updateData['note'] = $note;
                    $attendance->update($updateData);
                } else {
                    // Belum ada data sama sekali, buat baru
                    Attendance::create([
                        'user_id'  => $userId,
                        'date'     => $date,
                        'check_in' => $time,
                        'status'   => 'Hadir',
                        'note'     => $note,
                    ]);
                }
            } elseif ($subType === 'Absen Pulang') {
                if ($attendance) {
                    // Update check_out pada data yang sudah ada
                    $updateData = ['check_out' => $time];
                    if ($note && !$attendance->note) $updateData['note'] = $note;
                    $attendance->update($updateData);
                } else {
                    // Tidak ada check_in sama sekali — buat data dengan check_out saja
                    Attendance::create([
                        'user_id'   => $userId,
                        'date'      => $date,
                        'check_out' => $time,
                        'status'    => 'Hadir',
                        'note'      => $note,
                    ]);
                }
            }
        }
        // ────────────────────────────────────────────────────────────────────

        return back()->with('success', 'Pengajuan disetujui dan absensi karyawan telah diperbarui secara otomatis.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id()
        ]);

        return back()->with('success', 'Pengajuan izin ditolak.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return back()->with('success', 'Pengajuan izin berhasil dihapus.');
    }
}
