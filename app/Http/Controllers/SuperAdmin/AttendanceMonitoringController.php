<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DeletedAttendance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceMonitoringController extends Controller
{
    private function isHenrySuperAdmin(?User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        $name     = strtolower(trim((string) $user->name));
        $username = strtolower(trim((string) ($user->username ?? '')));

        return $name === 'henry' || $username === 'henry';
    }

    public function index(Request $request): View
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if ($this->isHenrySuperAdmin($currentUser)) {
            $employees = User::whereHas('role', function ($q) {
                $q->where('slug', 'karyawan');
            })->where('is_active', true)->orderBy('name')->get();

            $employeeId = $request->query('employee_id');
            $month = $request->query('month', Carbon::now()->format('Y-m'));
            $start = Carbon::parse($month . '-01');
            $end = $start->copy()->endOfMonth();

            $report = null;
            $allReports = collect();

            if ($employeeId) {
                $attendances = Attendance::query()
                    ->where('user_id', $employeeId)
                    ->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString())
                    ->orderBy('date')
                    ->get();

                $leaves = LeaveRequest::query()
                    ->where('user_id', $employeeId)
                    ->whereDate('start_date', '>=', $start->toDateString())
                    ->whereDate('start_date', '<=', $end->toDateString())
                    ->orderBy('start_date')
                    ->get();

                $summary = [
                    'total_present' => $attendances->where('status', 'Hadir')->count(),
                    'total_late' => $attendances->where('status', 'Terlambat')->count(),
                    'total_leave' => $leaves->count(),
                    'total_sick' => $leaves->where('type', 'Sakit')->count(),
                ];

                $report = [
                    'attendances' => $attendances,
                    'leaves' => $leaves,
                    'summary' => $summary,
                    'employee' => User::whereKey($employeeId)->first(),
                    'month' => $month,
                ];
            } else {
                $allReports = $employees->map(function ($employee) use ($start, $end) {
                    $attendances = Attendance::query()
                        ->where('user_id', $employee->id)
                        ->whereDate('date', '>=', $start->toDateString())
                        ->whereDate('date', '<=', $end->toDateString())
                        ->get();

                    $leaves = LeaveRequest::query()
                        ->where('user_id', $employee->id)
                        ->whereDate('start_date', '>=', $start->toDateString())
                        ->whereDate('start_date', '<=', $end->toDateString())
                        ->get();

                    return [
                        'employee' => $employee,
                        'summary' => [
                            'total_present' => $attendances->where('status', 'Hadir')->count(),
                            'total_late' => $attendances->where('status', 'Terlambat')->count(),
                            'total_leave' => $leaves->count(),
                            'total_sick' => $leaves->where('type', 'Sakit')->count(),
                            'total_attendance_records' => $attendances->count(),
                        ],
                    ];
                });
            }

            return view('pic.reports.index', [
                'employees' => $employees,
                'report' => $report,
                'allReports' => $allReports,
                'employeeId' => $employeeId,
                'month' => $month,
                'reportRouteName' => 'super-admin.attendance.index',
            ]);
        }

        $date = $request->get('date', Carbon::today()->toDateString());

        $attendances = Attendance::with(['user.division'])
            ->whereDate('date', $date)
            ->latest()
            ->get();

        // Daftar karyawan aktif untuk dropdown absen manual
        $employees = User::whereHas('role', fn($q) => $q->where('slug', 'karyawan'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('super-admin.attendance.index', compact('attendances', 'date', 'employees'));
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

    /**
     * Absen Masuk Manual oleh Super Admin JMN
     * Digunakan saat maintenance/error server agar absen karyawan tidak kosong.
     */
    public function manualCheckin(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'date'       => 'required|date',
            'check_in'   => 'required|date_format:H:i',
            'status'     => 'required|in:Hadir,Terlambat,Izin,Sakit',
            'note'       => 'nullable|string|max:255',
        ]);

        $targetDate = Carbon::parse($request->date)->toDateString();
        $employee   = User::findOrFail($request->user_id);

        // Cek apakah sudah ada absensi di tanggal tersebut
        $existing = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $targetDate)
            ->first();

        if ($existing && $existing->check_in) {
            return back()->with('error', "Karyawan {$employee->name} sudah memiliki absen masuk pada tanggal {$targetDate}.");
        }

        // Hitung lateness_minutes berdasarkan setting
        $settings        = Setting::all()->pluck('value', 'key');
        $checkInTime     = Carbon::parse($targetDate . ' ' . $request->check_in . ':00');
        $latenessMinutes = 0;
        $status          = $request->status;

        $userDivision    = $employee->division ? strtolower(trim($employee->division->name)) : '';
        $isLiveStreaming  = str_contains($userDivision, 'live streaming');

        if (!$isLiveStreaming) {
            $expectedCheckIn = Carbon::parse($targetDate . ' ' . ($settings['check_in_time'] ?? '08:00') . ':00');
            $graceMinutes    = (int)($settings['late_tolerance_minutes'] ?? 15);
            if ($checkInTime->greaterThan($expectedCheckIn->addMinutes($graceMinutes))) {
                $latenessMinutes = $expectedCheckIn->diffInMinutes($checkInTime);
                $status = 'Terlambat';
            }
        }

        if ($existing) {
            $existing->update([
                'check_in'        => $request->check_in . ':00',
                'status'          => $status,
                'lateness_minutes' => $latenessMinutes,
                'is_pulang_cepat' => false,
                'note'            => $request->note ?? 'Absen Manual oleh Admin',
                'lat'             => 0,
                'long'            => 0,
            ]);
        } else {
            Attendance::create([
                'user_id'         => $employee->id,
                'date'            => $targetDate,
                'check_in'        => $request->check_in . ':00',
                'check_out'       => null,
                'status'          => $status,
                'lateness_minutes' => $latenessMinutes,
                'is_pulang_cepat' => false,
                'note'            => $request->note ?? 'Absen Manual oleh Admin',
                'lat'             => 0,
                'long'            => 0,
            ]);
        }

        return redirect()
            ->route('super-admin.attendance.index', ['date' => $targetDate])
            ->with('success', "✅ Absen masuk manual untuk {$employee->name} pada {$targetDate} berhasil dicatat.");
    }

    /**
     * Absen Pulang Manual oleh Super Admin JMN
     */
    public function manualCheckout(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'date'       => 'required|date',
            'check_out'  => 'required|date_format:H:i',
            'note'       => 'nullable|string|max:255',
        ]);

        $targetDate = Carbon::parse($request->date)->toDateString();
        $employee   = User::findOrFail($request->user_id);
        $userDivision = $employee->division ? strtolower(trim($employee->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        // Cari attendance hari ini, atau kemarin (untuk live streaming yang shift malam)
        $attendance = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $targetDate)
            ->first();

        // Jika live streaming & tidak ada absen hari ini, cek kemarin
        if (!$attendance && $isLiveStreaming) {
            $attendance = Attendance::where('user_id', $employee->id)
                ->whereDate('date', Carbon::parse($targetDate)->subDay()->toDateString())
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->first();
        }

        if (!$attendance) {
            return back()->with('error', "Tidak ditemukan absen masuk untuk {$employee->name} pada tanggal {$targetDate}. Buat absen masuk terlebih dahulu.");
        }

        if ($attendance->check_out) {
            return back()->with('error', "Karyawan {$employee->name} sudah memiliki absen pulang.");
        }

        // Hitung is_pulang_cepat
        $isPulangCepat = false;
        $checkOutTime  = Carbon::parse($targetDate . ' ' . $request->check_out . ':00');

        if (str_contains($userDivision, 'gudang')) {
            $isPulangCepat = $request->check_out < '18:00';
        } elseif ($attendance->check_in) {
            $attendanceDateStr = $attendance->date instanceof Carbon
                ? $attendance->date->toDateString()
                : explode(' ', (string)$attendance->date)[0];
            $clockInTime   = Carbon::parse($attendanceDateStr . ' ' . $attendance->check_in);
            $minutesWorked = $clockInTime->diffInMinutes($checkOutTime);
            $isPulangCepat = $minutesWorked < (8 * 60);
        }

        $attendance->update([
            'check_out'       => $request->check_out . ':00',
            'is_pulang_cepat' => $isPulangCepat,
            'status'          => 'Hadir',
            'note'            => ($attendance->note ? $attendance->note . ' | ' : '') . ($request->note ?? 'Absen Pulang Manual oleh Admin'),
        ]);

        return redirect()
            ->route('super-admin.attendance.index', ['date' => $targetDate])
            ->with('success', "✅ Absen pulang manual untuk {$employee->name} berhasil dicatat" . ($isPulangCepat ? ' (Pulang Cepat).' : '.'));
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
