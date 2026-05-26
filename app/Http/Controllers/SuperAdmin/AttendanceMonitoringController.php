<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DeletedAttendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $attendances = Attendance::with(['user.division'])
            ->whereDate('date', $date)
            ->latest()
            ->get();

        return view('super-admin.attendance.index', compact('attendances', 'date'));
    }

    public function destroy(Request $request, Attendance $attendance): RedirectResponse
    {
        $attendance->load(['user.division']);
        $date = $attendance->date instanceof Carbon
            ? $attendance->date->toDateString()
            : (string) $attendance->date;

        $attendanceId = $attendance->id;

        DB::transaction(function () use ($attendance, $attendanceId) {
            DeletedAttendance::create([
                'original_attendance_id' => $attendanceId,
                'user_id' => $attendance->user_id,
                'user_name' => $attendance->user->name,
                'division_name' => $attendance->user->division->name ?? null,
                'date' => $attendance->date,
                'payload' => $attendance->toArray(),
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
            ]);

            Attendance::query()->whereKey($attendanceId)->delete();
        });

        return redirect()
            ->route('super-admin.attendance.index', ['date' => $request->get('date', $date)])
            ->with('success', 'Absensi berhasil dihapus dan dicadangkan.');
    }

    public function deletedBackups(Request $request): View
    {
        $query = DeletedAttendance::with(['deletedByUser'])
            ->orderByDesc('deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('division_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->string('date')->toString());
        }

        $deletedBackups = $query->paginate(20)->withQueryString();

        return view('super-admin.attendance.deleted-backups', compact('deletedBackups'));
    }

    public function restore(DeletedAttendance $deletedBackup): RedirectResponse
    {
        $payload = $deletedBackup->payload;
        $dateString = $deletedBackup->date instanceof Carbon
            ? $deletedBackup->date->toDateString()
            : (string) $deletedBackup->date;

        $exists = Attendance::query()
            ->where('user_id', '=', $deletedBackup->user_id)
            ->where('date', '=', $dateString)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('super-admin.attendance.deleted-backups')
                ->with('error', 'Tidak dapat memulihkan: sudah ada absensi aktif untuk karyawan dan tanggal yang sama.');
        }

        Attendance::create([
            'user_id' => $payload['user_id'] ?? $deletedBackup->user_id,
            'check_in' => $payload['check_in'] ?? null,
            'check_out' => $payload['check_out'] ?? null,
            'date' => $payload['date'] ?? $dateString,
            'status' => $payload['status'] ?? 'Hadir',
            'lat' => $payload['lat'] ?? null,
            'long' => $payload['long'] ?? null,
            'photo' => $payload['photo'] ?? null,
            'lateness_minutes' => $payload['lateness_minutes'] ?? null,
            'is_pulang_cepat' => $payload['is_pulang_cepat'] ?? false,
        ]);

        DeletedAttendance::destroy($deletedBackup->id);

        return redirect()
            ->route('super-admin.attendance.deleted-backups')
            ->with('success', 'Absensi berhasil dipulihkan dari cadangan.');
    }
}
