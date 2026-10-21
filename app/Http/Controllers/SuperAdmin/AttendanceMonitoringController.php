<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DeletedAttendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
                $q->whereIn('slug', ['karyawan', 'karyawan_ramayana']);
            })->where('is_active', true)->orderBy('name')->get();

            $employeeId = $request->query('employee_id');
            $month      = $request->query('month', Carbon::now()->format('Y-m'));
            $start      = Carbon::parse($month . '-01');
            $end        = $start->copy()->endOfMonth();

            $report     = null;
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
                    'total_late'    => $attendances->where('status', 'Terlambat')->count(),
                    'total_leave'   => $leaves->count(),
                    'total_sick'    => $leaves->where('type', 'Sakit')->count(),
                ];

                $report = [
                    'attendances' => $attendances,
                    'leaves'      => $leaves,
                    'summary'     => $summary,
                    'employee'    => User::whereKey($employeeId)->first(),
                    'month'       => $month,
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
                        'summary'  => [
                            'total_present'            => $attendances->where('status', 'Hadir')->count(),
                            'total_late'               => $attendances->where('status', 'Terlambat')->count(),
                            'total_leave'              => $leaves->count(),
                            'total_sick'               => $leaves->where('type', 'Sakit')->count(),
                            'total_attendance_records' => $attendances->count(),
                        ],
                    ];
                });
            }

            return view('pic.reports.index', [
                'employees'       => $employees,
                'report'          => $report,
                'allReports'      => $allReports,
                'employeeId'      => $employeeId,
                'month'           => $month,
                'reportRouteName' => 'super-admin.attendance.index',
            ]);
        }

        $date = $request->get('date', Carbon::today()->toDateString());

        $attendances = Attendance::has('user')->with(['user.division'])
            ->whereDate('date', $date)
            ->latest()
            ->get();

        // Daftar karyawan aktif untuk dropdown absen manual
        $employees = User::with(['role', 'division'])
            ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan', 'karyawan_ramayana']))
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
                'user_id'                => $attendance->user_id,
                'user_name'              => $attendance->user->name,
                'division_name'          => $attendance->user->division->name ?? null,
                'date'                   => $attendance->date,
                'payload'                => $attendance->toArray(),
                'deleted_by'             => Auth::id(),
                'deleted_at'             => now(),
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
            'user_id'  => 'required|exists:users,id',
            'date'     => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'status'   => 'required|in:Hadir,Terlambat,Izin,Sakit',
            'note'     => 'nullable|string|max:255',
        ]);

        $targetDate = Carbon::parse($request->date)->toDateString();
        $employee   = User::findOrFail($request->user_id);

        /** @var Attendance|null $existing */
        $existing = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $targetDate)
            ->first();

        // Guard: sudah ada check_in (Dinonaktifkan agar Super Admin bisa menindih/overwrite)
        /*
        if ($existing instanceof Attendance && !empty($existing->check_in)) {
            return back()->with('error', "Karyawan {$employee->name} sudah memiliki absen masuk pada tanggal {$targetDate}.");
        }
        */

        // Hitung lateness_minutes berdasarkan setting
        $settings        = Setting::all()->pluck('value', 'key');
        $checkInTime     = Carbon::parse($targetDate . ' ' . $request->check_in . ':00');
        $latenessMinutes = 0;
        $status          = $request->status;

        $userDivision   = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        $isSalesMarketing = str_contains($userDivision, 'sales marketing');

        if (!$isLiveStreaming && !$isSalesMarketing) {
            // Staff kantor dan divisi lain: terlambat jika check-in jam 10.00 ke atas
            $lateThreshold = Carbon::parse($targetDate . ' 10:00:00');

            if ($checkInTime->greaterThanOrEqualTo($lateThreshold)) {
                $latenessMinutes = (int) $lateThreshold->diffInMinutes($checkInTime);
                $status          = 'Terlambat';
            }
        }
        // Sales Marketing & Live Streaming: tidak ada terlambat (status tetap dari input form)

        if ($existing instanceof Attendance) {
            $existing->update([
                'check_in'         => $request->check_in . ':00',
                'status'           => $status,
                'lateness_minutes' => $latenessMinutes,
                'is_pulang_cepat'  => false,
                'note'             => $request->note ?? 'Absen Manual oleh Admin',
                'is_locked'        => true,
                'locked_by'        => 'Super Admin',
                'lat'              => 0,
                'long'             => 0,
            ]);
        } else {
            Attendance::create([
                'user_id'          => $employee->id,
                'date'             => $targetDate,
                'check_in'         => $request->check_in . ':00',
                'check_out'        => null,
                'status'           => $status,
                'lateness_minutes' => $latenessMinutes,
                'is_pulang_cepat'  => false,
                'note'             => $request->note ?? 'Absen Manual oleh Admin',
                'is_locked'        => true,
                'locked_by'        => 'Super Admin',
                'lat'              => 0,
                'long'             => 0,
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
            'user_id'   => 'required|exists:users,id',
            'date'      => 'required|date',
            'check_out' => 'required|date_format:H:i',
            'note'      => 'nullable|string|max:255',
        ]);

        $targetDate     = Carbon::parse($request->date)->toDateString();
        $employee       = User::findOrFail($request->user_id);
        $userDivision   = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        /** @var Attendance|null $attendance */
        $attendance = Attendance::where('user_id', $employee->id)
            ->whereDate('date', $targetDate)
            ->first();

        // Jika live streaming & tidak ada absen hari ini, cek kemarin
        if (!$attendance instanceof Attendance && $isLiveStreaming) {
            /** @var Attendance|null $attendance */
            $attendance = Attendance::where('user_id', $employee->id)
                ->whereDate('date', Carbon::parse($targetDate)->subDay()->toDateString())
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->first();
        }

        if (!$attendance instanceof Attendance) {
            return back()->with('error', "Tidak ditemukan absen masuk untuk {$employee->name} pada tanggal {$targetDate}. Buat absen masuk terlebih dahulu.");
        }

        // Guard: sudah ada check_out (Dinonaktifkan agar Super Admin bisa menindih/overwrite)
        /*
        if (!empty($attendance->check_out)) {
            return back()->with('error', "Karyawan {$employee->name} sudah memiliki absen pulang.");
        }
        */

        // Hitung is_pulang_cepat
        $isPulangCepat = false;
        $checkOutTime  = Carbon::parse($targetDate . ' ' . $request->check_out . ':00');

        if ($employee->role->slug === 'karyawan_ramayana') {
            if (!empty($attendance->check_in)) {
                $attendanceDateStr = $attendance->date instanceof Carbon
                    ? $attendance->date->toDateString()
                    : explode(' ', (string) $attendance->date)[0];

                $clockInTime   = Carbon::parse($attendanceDateStr . ' ' . $attendance->check_in);
                $minutesWorked = (int) $clockInTime->diffInMinutes($checkOutTime);
                $isPulangCepat = $minutesWorked < (7 * 60);
            }
        } elseif (str_contains($userDivision, 'gudang')) {
            $isPulangCepat = $request->check_out < '18:00';
        } elseif (!empty($attendance->check_in)) {
            $attendanceDateStr = $attendance->date instanceof Carbon
                ? $attendance->date->toDateString()
                : explode(' ', (string) $attendance->date)[0];

            $clockInTime   = Carbon::parse($attendanceDateStr . ' ' . $attendance->check_in);
            $minutesWorked = (int) $clockInTime->diffInMinutes($checkOutTime);
            $isPulangCepat = $minutesWorked < (8 * 60);
        }

        $note = $request->note ?? 'Absen Pulang Manual oleh Admin';
        $existingNote = !empty($attendance->note) ? $attendance->note : '';
        if (!empty($existingNote)) {
            if (!str_contains($existingNote, $note)) {
                $newNote = $existingNote . ' | ' . $note;
            } else {
                $newNote = $existingNote;
            }
        } else {
            $newNote = $note;
        }

        $attendance->update([
            'check_out'       => $request->check_out . ':00',
            'is_pulang_cepat' => $isPulangCepat,
            'status'          => 'Hadir',
            'is_locked'       => true,
            'locked_by'       => 'Super Admin',
            'note'            => $newNote,
        ]);

        return redirect()
            ->route('super-admin.attendance.index', ['date' => $targetDate])
            ->with('success', "✅ Absen pulang manual untuk {$employee->name} berhasil dicatat" . ($isPulangCepat ? ' (Pulang Cepat).' : '.'));
    }

    /**
     * Atur Cuti / Izin Karyawan oleh Super Admin
     * Membuat pengajuan cuti otomatis disetujui dan mengisi status Cuti di absensi.
     */
    public function manualLeave(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'type'       => 'required|string|in:Cuti,Izin,Sakit',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:255',
        ]);

        $employee  = User::findOrFail($request->user_id);
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->startOfDay();
        $type      = $request->type;
        $reason    = $request->reason ? trim($request->reason) : "{$type} (Diatur Super Admin)";

        DB::transaction(function () use ($employee, $startDate, $endDate, $type, $reason) {
            // 1. Buat record LeaveRequest berstatus approved
            LeaveRequest::create([
                'user_id'          => $employee->id,
                'type'             => $type,
                'start_date'       => $startDate->toDateString(),
                'end_date'         => $endDate->toDateString(),
                'reason'           => $reason,
                'status'           => 'approved',
                'approved_by'      => Auth::id(),
                'admin_message'    => 'Diatur langsung oleh Super Admin',
                'admin_message_by' => Auth::id(),
                'admin_message_at' => now(),
            ]);

            // 2. Buat atau update record Attendance untuk setiap hari dalam rentang tanggal
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dateStr = $cursor->toDateString();

                $attendance = Attendance::where('user_id', $employee->id)
                    ->whereDate('date', $dateStr)
                    ->first();

                if ($attendance) {
                    $attendance->update([
                        'status'          => $type,
                        'note'            => $reason,
                        'check_in'        => null,
                        'check_out'       => null,
                        'is_pulang_cepat' => false,
                        'is_locked'       => true,
                        'locked_by'       => 'Super Admin',
                    ]);
                } else {
                    Attendance::create([
                        'user_id'   => $employee->id,
                        'date'      => $dateStr,
                        'status'    => $type,
                        'note'      => $reason,
                        'is_locked' => true,
                        'locked_by' => 'Super Admin',
                    ]);
                }

                $cursor->addDay();
            }
        });

        $dateFormatted = $startDate->equalTo($endDate)
            ? $startDate->locale('id')->translatedFormat('d M Y')
            : $startDate->locale('id')->translatedFormat('d M Y') . ' s/d ' . $endDate->locale('id')->translatedFormat('d M Y');

        return redirect()
            ->route('super-admin.attendance.index', ['date' => $startDate->toDateString()])
            ->with('success', "✅ {$type} untuk {$employee->name} berhasil diatur pada {$dateFormatted}.");
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
        $payload    = $deletedBackup->payload;
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
            'user_id'          => $payload['user_id'] ?? $deletedBackup->user_id,
            'check_in'         => $payload['check_in'] ?? null,
            'check_out'        => $payload['check_out'] ?? null,
            'date'             => $payload['date'] ?? $dateString,
            'status'           => $payload['status'] ?? 'Hadir',
            'lat'              => $payload['lat'] ?? null,
            'long'             => $payload['long'] ?? null,
            'photo'            => $payload['photo'] ?? null,
            'lateness_minutes' => $payload['lateness_minutes'] ?? null,
            'is_pulang_cepat'  => $payload['is_pulang_cepat'] ?? false,
        ]);

        DeletedAttendance::destroy($deletedBackup->id);

        return redirect()
            ->route('super-admin.attendance.deleted-backups')
            ->with('success', 'Absensi berhasil dipulihkan dari cadangan.');
    }

    /**
     * Rekap Absensi Bulanan — Akumulasi kehadiran semua karyawan
     * Menampilkan: masuk, tidak hadir (beserta tanggal), lupa absen (beserta tanggal)
     */
    public function rekapBulanan(Request $request): View
    {
        // Default ke bulan yang baru di-import jika ada, atau bulan ini (bersihkan jika ada nilai masa depan yang korup)
        $sessionMonth = session('imported_month');
        if ($sessionMonth) {
            try {
                if (Carbon::parse($sessionMonth . '-01')->year > (int) date('Y')) {
                    $sessionMonth = null;
                    session()->forget('imported_month');
                }
            } catch (\Throwable $e) {
                $sessionMonth = null;
                session()->forget('imported_month');
            }
        }
        $month = $request->query('month', $sessionMonth ?: Carbon::now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();
        $today = Carbon::today();

        // Hari-hari efektif yang dihitung (dari awal bulan s.d hari ini atau akhir bulan)
        $effectiveEnd = $end->lt($today) ? $end : $today->copy()->endOfDay();

        // Ambil semua karyawan aktif (semua role karyawan)
        $employees = User::with(['role', 'division'])
            ->whereHas('role', fn($q) => $q->whereIn('slug', ['karyawan', 'karyawan_ramayana']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil semua absensi bulan ini dalam 1 query
        $allAttendances = Attendance::whereIn('user_id', $employees->pluck('id'))
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $effectiveEnd->toDateString())
            ->get()
            ->groupBy('user_id');

        // Ambil pengajuan izin / libur bulan ini
        $allLeaves = LeaveRequest::whereIn('user_id', $employees->pluck('id'))
            ->whereDate('start_date', '<=', $effectiveEnd->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->whereIn('status', ['approved', 'pending'])
            ->get()
            ->groupBy('user_id');

        // Ambil hari libur di bulan ini
        $allHolidays = Holiday::whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get();

        $rekap = [];

        foreach ($employees as $emp) {
            $attendances   = $allAttendances->get($emp->id, collect());
            $leaves        = $allLeaves->get($emp->id, collect());
            $divisionName  = strtolower(trim($emp->division?->name ?? ''));
            $isStaffKantor = str_contains($divisionName, 'staff kantor');
            $isRamayana    = $emp->role?->slug === 'karyawan_ramayana';

            // Filter hari libur yang berlaku untuk divisi karyawan ini (atau libur nasional/semua divisi)
            $empHolidays = $allHolidays->filter(function ($h) use ($emp) {
                return is_null($h->division_id) || $h->division_id == $emp->division_id;
            })->keyBy(fn($h) => Carbon::parse($h->date)->toDateString());

            // Lookup attendance & leaves
            $attByDate = $attendances->keyBy(fn($a) => Carbon::parse($a->date)->toDateString());

            $leaveDates = [];
            foreach ($leaves as $lr) {
                $c = Carbon::parse($lr->start_date)->startOfDay();
                $cEnd = Carbon::parse($lr->end_date)->startOfDay();
                while ($c->lte($cEnd)) {
                    $leaveDates[$c->toDateString()] = $lr;
                    $c->addDay();
                }
            }

            $masukList      = [];
            $lupaAbsenList  = [];
            $tidakHadirList = [];

            $cursor = $start->copy();
            while ($cursor->lte($effectiveEnd)) {
                $dateStr       = $cursor->toDateString();
                $dayName       = $cursor->locale('id')->translatedFormat('l');
                $formattedDate = $cursor->locale('id')->translatedFormat('d M Y');
                $label         = "{$dayName}, {$formattedDate}";
                $holiday       = $empHolidays->get($dateStr);
                $isHoliday     = $holiday !== null;
                $isWeekend     = $cursor->isWeekend();

                /** @var Attendance|null $att */
                $att   = $attByDate->get($dateStr);
                $leave = $leaveDates[$dateStr] ?? null;

                if ($att) {
                    $attStatus = trim((string)$att->status);

                    // 1. Cek apakah record absensi ini adalah libur (disetup super admin atau placeholder libur)
                    if (str_starts_with($attStatus, 'Libur') || in_array($attStatus, ['Libur', 'Libur (Day Off)'])) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => $attStatus,
                            'is_holiday' => true,
                        ];
                    } elseif ($attStatus === 'Sakit') {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Sakit' . ($att->note ? ': ' . $att->note : ''),
                            'is_holiday' => false,
                            'is_cuti'    => false,
                        ];
                    } elseif (strcasecmp($attStatus, 'Cuti') === 0 || str_contains(strtolower($attStatus), 'cuti')) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Cuti' . ($att->note ? ': ' . $att->note : ''),
                            'is_holiday' => false,
                            'is_cuti'    => true,
                        ];
                    } elseif (strcasecmp($attStatus, 'Izin') === 0 || str_contains(strtolower($attStatus), 'izin')) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Izin' . ($att->note ? ': ' . $att->note : ''),
                            'is_holiday' => false,
                            'is_cuti'    => false,
                        ];
                    } elseif (in_array($attStatus, ['Alpa', 'Tidak Hadir'])) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Tidak Hadir' . ($att->note ? ': ' . $att->note : ''),
                            'is_holiday' => false,
                            'is_cuti'    => false,
                        ];
                    } else {
                        // Absensi masuk / terlambat / hadir riil
                        $checkInRaw  = $att->check_in;
                        $checkOutRaw = $att->check_out;
                        $checkIn     = $checkInRaw ? Carbon::parse($checkInRaw)->format('H.i') : null;
                        $checkOut    = $checkOutRaw ? Carbon::parse($checkOutRaw)->format('H.i') : null;

                        $isLupaMasuk  = empty($checkInRaw) && !empty($checkOutRaw);
                        $isLupaPulang = (!empty($checkInRaw) && empty($checkOutRaw) && !$cursor->isToday());

                        // 1. Kolom TENGAH: Lupa Absen (Gabungan masuk dan pulang dijadikan 1 kolom)
                        if ($isLupaMasuk) {
                            $lupaAbsenList[] = [
                                'label'  => $label,
                                'detail' => "Pulang {$checkOut}, tidak absen masuk",
                                'type'   => 'masuk',
                            ];
                        } elseif ($isLupaPulang) {
                            $lupaAbsenList[] = [
                                'label'  => $label,
                                'detail' => "Masuk {$checkIn}, tidak absen pulang",
                                'type'   => 'pulang',
                            ];
                        }

                        // 2. Kolom KIRI: Masuk (Tetap diikutkan masuk meski lupa absen, tapi ditulis keterangan lupa absen)
                        $lupaTag = null;
                        if ($isLupaMasuk) {
                            $lupaTag = 'Lupa Absen Masuk';
                        } elseif ($isLupaPulang) {
                            $lupaTag = 'Lupa Absen Pulang';
                        }

                        // Format jam masuk & pulang
                        $jamDetail = '';
                        if ($checkIn && $checkOut) {
                            $jamDetail = "Masuk {$checkIn} &bull; Pulang {$checkOut}";
                        } elseif ($checkIn) {
                            $jamDetail = "Masuk {$checkIn} &bull; Pulang &mdash;";
                        } elseif ($checkOut) {
                            $jamDetail = "Masuk &mdash; &bull; Pulang {$checkOut}";
                        } else {
                            $jamDetail = "Tercatat hadir (tanpa jam)";
                        }

                        $masukList[] = [
                            'label'      => $label,
                            'jam_detail' => $jamDetail,
                            'status'     => $att->status ?? 'Hadir',
                            'lupa_tag'   => $lupaTag,
                            'is_lupa'    => ($isLupaMasuk || $isLupaPulang),
                            'note'       => $att->note,
                        ];
                    }
                } elseif ($leave) {
                    // Pengajuan Izin / Libur / Sakit / Cuti
                    $type = $leave->type;
                    $typeLower = strtolower($type);
                    if (in_array($type, ['Libur', 'Libur (Day Off)'])) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Libur (Day Off)',
                            'is_holiday' => true,
                            'is_cuti'    => false,
                        ];
                    } elseif ($type === 'Sakit') {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Sakit' . ($leave->reason ? ': ' . $leave->reason : ''),
                            'is_holiday' => false,
                            'is_cuti'    => false,
                        ];
                    } elseif (str_contains($typeLower, 'cuti')) {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Cuti' . ($leave->reason ? ': ' . $leave->reason : ''),
                            'is_holiday' => false,
                            'is_cuti'    => true,
                        ];
                    } else {
                        $tidakHadirList[] = [
                            'label'      => $label,
                            'keterangan' => 'Izin: ' . ($leave->reason ?? $type),
                            'is_holiday' => false,
                            'is_cuti'    => false,
                        ];
                    }
                } else {
                    // Tidak ada absensi dan tidak ada izin
                    $keterangan   = 'Tidak Absen';
                    $isHolidayDay = false;

                    if ($isHoliday) {
                        $keterangan   = 'Libur: ' . $holiday->description;
                        $isHolidayDay = true;
                    } elseif ($isStaffKantor && $isWeekend) {
                        $keterangan   = 'Libur Akhir Pekan';
                        $isHolidayDay = true;
                    } elseif ($isRamayana && $cursor->isSunday()) {
                        $keterangan   = 'Libur Hari Minggu';
                        $isHolidayDay = true;
                    }

                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => $keterangan,
                        'is_holiday' => $isHolidayDay,
                    ];
                }

                $cursor->addDay();
            }

            $rekap[] = [
                'employee'          => $emp,
                'total_masuk'       => count($masukList),
                'masuk_list'        => $masukList,
                'total_lupa_absen'  => count($lupaAbsenList),
                'lupa_absen_list'   => $lupaAbsenList,
                'total_tidak_hadir' => count($tidakHadirList),
                'tidak_hadir_list'  => $tidakHadirList,
                'total_records'     => $attendances->count(),
            ];
        }

        // Sort: yang paling banyak masuk di atas
        usort($rekap, fn($a, $b) => $b['total_masuk'] <=> $a['total_masuk']);

        return view('super-admin.attendance.rekap-bulanan', compact('rekap', 'month', 'employees'));
    }

    /**
     * Helper parser tanggal untuk import absensi (Mendukung DD/MM/YYYY, YYYY-MM-DD, Excel Serial)
     */
    private function parseImportDate($raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $raw = trim((string) $raw);

        // 1. Excel serial number (antara tahun 1970 - 2060: ~25569 sampai ~60000)
        if (is_numeric($raw) && (float) $raw > 25569 && (float) $raw < 60000) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw)
                )->toDateString();
            } catch (\Throwable $e) {}
        }

        $currentYear = (int) date('Y');

        // 2. Format YYYY-MM-DD atau YYYY/MM/DD atau YYYY.MM.DD
        if (preg_match('/^(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})$/', $raw, $m)) {
            $year  = (int) $m[1];
            $month = (int) $m[2];
            $day   = (int) $m[3];
            if ($year > $currentYear) {
                $year = $currentYear;
            }
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // 3. Format DD-MM-YYYY atau DD/MM/YYYY atau DD.MM.YYYY (Standar Indonesia)
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $raw, $m)) {
            $year  = (int) $m[3];
            $month = (int) $m[2];
            $day   = (int) $m[1];
            if ($year > $currentYear) {
                $year = $currentYear;
            }
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // 4. Format DD-MM-YY atau DD/MM/YY atau DD.MM.YY
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{2})$/', $raw, $m)) {
            $year  = 2000 + (int) $m[3];
            $month = (int) $m[2];
            $day   = (int) $m[1];
            if ($year > $currentYear) {
                $year = $currentYear;
            }
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // 5. Fallback via Carbon
        try {
            $parsed = Carbon::parse($raw);
            if ($parsed->year > $currentYear) {
                $parsed->year($currentYear);
            }
            return $parsed->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Helper parser jam untuk import absensi (Mendukung HH:MM, HH,MM koma Indonesia, HH.MM, Excel Serial/Fraction)
     */
    private function parseImportTime($raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Jika numeric
        if (is_numeric($raw)) {
            $floatVal = (float) $raw;

            // A. Excel native time fraction (0.0 sampai < 1.0) misal 0.5486 -> 13:10:00
            if ($floatVal > 0.0 && $floatVal < 1.0) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($floatVal)->format('H:i:00');
                } catch (\Throwable $e) {}
            }

            // B. Excel DateTime serial (> 25569)
            if ($floatVal >= 25569) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($floatVal)->format('H:i:00');
                } catch (\Throwable $e) {}
            }
        }

        $str = trim((string) $raw);

        // Format HH:MM:SS atau HH:MM
        if (preg_match('/^(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $str, $m)) {
            $hour = (int) $m[1];
            $min  = (int) $m[2];
            $sec  = isset($m[3]) ? (int) $m[3] : 0;
            if ($min >= 60) {
                $hour += intdiv($min, 60);
                $min   = $min % 60;
            }
            $hour = $hour % 24;
            return sprintf('%02d:%02d:%02d', $hour, $min, $sec);
        }

        // Format HH,MM atau HH.MM (Standar input Indonesia, misal: 13,10 atau 7,49 atau 13.1 atau 21,42)
        if (preg_match('/^(\d{1,2})[,\.](\d{1,2})$/', $str, $m)) {
            $hour = (int) $m[1];
            // Jika 1 digit di belakang desimal (misal 13.1 dari 13.10), pad jadi 2 digit: 10
            $minStr = strlen($m[2]) === 1 ? $m[2] . '0' : $m[2];
            $min    = (int) $minStr;
            if ($min >= 60) {
                $hour += intdiv($min, 60);
                $min   = $min % 60;
            }
            $hour = $hour % 24;
            return sprintf('%02d:%02d:00', $hour, $min);
        }

        // Format cuma jam bulat, misal '13' atau '8'
        if (preg_match('/^(\d{1,2})$/', $str, $m)) {
            $hour = (int) $m[1];
            if ($hour >= 0 && $hour <= 24) {
                return sprintf('%02d:00:00', $hour % 24);
            }
        }

        // Fallback strtotime
        $ts = strtotime($str);
        if ($ts !== false) {
            return date('H:i:00', $ts);
        }

        return null;
    }

    /**
     * Download template Excel/CSV import absensi massal
     */
    public function downloadTemplate(Request $request)
    {
        $format = strtolower($request->query('format', 'xlsx'));

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Absensi');

        // ── Baris 1: Judul panduan
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', '📋 TEMPLATE IMPORT ABSENSI MASSAL — Format jam bebas: 13:10 atau 13,10 atau 13.10. Status boleh kosong (otomatis = Hadir). Nama cukup di baris pertama.');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '92400E']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2: Header Kolom
        $headers = [
            'A2' => 'Nama Karyawan',
            'B2' => 'Tanggal',
            'C2' => 'Jam Masuk',
            'D2' => 'Jam Pulang',
            'E2' => 'Status',
            'F2' => 'Catatan',
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '3730A3']]],
        ]);

        // ── Baris 3: Panduan format kolom (warna abu)
        $guides = [
            'A3' => 'Nama lengkap (cukup di baris pertama jika karyawan sama)',
            'B3' => 'DD/MM/YYYY atau YYYY-MM-DD (contoh: ' . date('d/m/Y') . ')',
            'C3' => 'HH:MM atau HH,MM (contoh: 08:00 atau 13,10)',
            'D3' => 'HH:MM atau HH,MM (contoh: 17:00 atau 21,42)',
            'E3' => 'Hadir / Terlambat / Izin / Sakit (boleh kosong)',
            'F3' => 'Keterangan tambahan (boleh kosong)',
        ];
        foreach ($guides as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
        ]);

        // Format kolom sebagai TEXT agar Excel tidak mengubah teks jam/tanggal secara aneh
        $sheet->getStyle('A:F')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // ── Baris 4–6: Contoh data
        $today      = date('d/m/Y');
        $sampleData = [
            4 => ['Budi Santoso',  $today, '08:00', '17:00', 'Hadir',  'Import massal'],
            5 => ['',              date('d/m/Y', strtotime('+1 day')), '08:15', '17:00', '',       ''],
            6 => ['Siti Aminah',   $today, '08:30', '17:00', '',       ''],
        ];
        foreach ($sampleData as $rowNum => $rowData) {
            $sheet->fromArray($rowData, null, 'A' . $rowNum);
            $fillColor = ($rowNum % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->applyFromArray([
                'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'font'    => ['size' => 10],
            ]);
        }

        // ── Lebar kolom yang optimal
        $columnWidths = ['A' => 32, 'B' => 20, 'C' => 18, 'D' => 18, 'E' => 28, 'F' => 28];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Freeze pane agar header tetap terlihat saat scroll
        $sheet->freezePane('A4');

        $fileName = 'template_import_absen_' . date('Ymd') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        if ($format === 'csv') {
            $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setUseBOM(true);
            $response = response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
        } else {
            $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $response = response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }

        return $response;
    }

    /**
     * Preview dan validasi data absensi dari Excel / CSV sebelum di-import
     */
    public function importPreview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $ext  = $file->getClientOriginalExtension();

        try {
            $rows = \App\Services\ExcelImportReader::readRows($path, $ext);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel/CSV: ' . $e->getMessage(),
            ], 422);
        }

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel/CSV kosong atau tidak memiliki data.',
            ], 422);
        }

        // Deteksi kolom dari baris header
        $headerRowIndex = null;
        $colNama        = null;
        $colTanggal     = null;
        $colCheckIn     = null;
        $colCheckOut    = null;
        $colStatus      = null;
        $colCatatan     = null;

        foreach ($rows as $index => $row) {
            $rowStr = implode(' ', array_map('strtolower', array_map('strval', $row)));
            if (str_contains($rowStr, 'template import')) {
                continue;
            }

            if (str_contains($rowStr, 'nama') || str_contains($rowStr, 'karyawan') || str_contains($rowStr, 'tanggal') || str_contains($rowStr, 'masuk')) {
                $headerRowIndex = $index;
                foreach ($row as $colIdx => $val) {
                    $v = strtolower(trim((string) $val));
                    if (str_contains($v, 'nama') || str_contains($v, 'karyawan')) $colNama = $colIdx;
                    elseif (str_contains($v, 'tanggal') || str_contains($v, 'date')) $colTanggal = $colIdx;
                    elseif (str_contains($v, 'masuk') || str_contains($v, 'check_in') || str_contains($v, 'check in')) $colCheckIn = $colIdx;
                    elseif (str_contains($v, 'pulang') || str_contains($v, 'check_out') || str_contains($v, 'check out')) $colCheckOut = $colIdx;
                    elseif (str_contains($v, 'status')) $colStatus = $colIdx;
                    elseif (str_contains($v, 'catatan') || str_contains($v, 'note')) $colCatatan = $colIdx;
                }
                break;
            }
        }

        if ($headerRowIndex === null) $headerRowIndex = 0;
        if ($colNama === null) $colNama = 0;
        if ($colTanggal === null) $colTanggal = 1;
        if ($colCheckIn === null) $colCheckIn = 2;
        if ($colCheckOut === null) $colCheckOut = 3;
        if ($colStatus === null) $colStatus = 4;
        if ($colCatatan === null) $colCatatan = 5;

        // Ambil seluruh user aktif untuk dropdown koreksi dan auto-matching
        $users = User::where('is_active', true)
            ->with(['role', 'division', 'location'])
            ->get()
            ->sortBy('name');

        $userMap  = [];
        $aliasMap = [];
        foreach ($users as $u) {
            $userMap[strtolower(trim((string) $u->name))] = $u;
            if (!empty($u->username)) {
                $userMap[strtolower(trim((string) $u->username))] = $u;
            }
            if (!empty($u->email)) {
                $userMap[strtolower(trim((string) $u->email))] = $u;
            }
            if (!empty($u->nickname)) {
                $aliases = preg_split('/[,;|]/', strtolower(trim((string) $u->nickname)));
                foreach ($aliases as $alias) {
                    $aliasClean = trim($alias);
                    if (!empty($aliasClean)) {
                        $aliasMap[$aliasClean] = $u;
                    }
                }
            }
        }

        $findUser = function (string $nameQuery) use ($userMap, $aliasMap, $users) {
            $clean = strtolower(preg_replace('/\s+/', ' ', trim($nameQuery)));
            if (empty($clean)) {
                return null;
            }

            // 1. Cocokkan persis dengan nama lengkap / username / email
            if (isset($userMap[$clean])) {
                return $userMap[$clean];
            }

            // 2. Cocokkan persis dengan alias / nama panggilan (misal: "rio" -> Muhammad Rio Pratama)
            if (isset($aliasMap[$clean])) {
                return $aliasMap[$clean];
            }

            // 3. Cocokkan jika alias ada di dalam query (atau sebaliknya)
            foreach ($aliasMap as $aliasKey => $u) {
                if ($aliasKey === $clean || str_starts_with($clean, $aliasKey . ' ') || str_contains($clean, $aliasKey)) {
                    return $u;
                }
            }

            // 4. Pencocokan Cerdas Kata (Word Token Matching)
            // Misalnya jika PIC Ramayana menulis "Rio", cari karyawan yang salah satu kata di namanya adalah "rio"
            $candidates = [];
            foreach ($users as $u) {
                $uName = strtolower(trim((string) $u->name));
                $uWords = array_filter(explode(' ', $uName), fn($w) => strlen($w) >= 2);
                $isRamayana = ($u->role?->slug === 'karyawan_ramayana') || str_contains(strtolower($u->division?->name ?? ''), 'ramayana');

                // Jika query satu kata persis cocok dengan salah satu kata di nama karyawan
                if (in_array($clean, $uWords, true)) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 100 : 80];
                    continue;
                }

                // Jika nama karyawan berawalan dari kata query
                if ($uName === $clean || str_starts_with($uName, $clean . ' ')) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 95 : 75];
                    continue;
                }

                // Substring
                if (str_contains($uName, $clean) && strlen($clean) >= 3) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 70 : 50];
                }
            }

            if (!empty($candidates)) {
                usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
                return $candidates[0]['user'];
            }

            return null;
        };

        $employeeList = $users->map(function ($u) {
            $dept = $u->division?->name ?? ($u->role?->name ?? '-');
            return [
                'id'       => $u->id,
                'name'     => $u->name,
                'dept'     => $dept,
                'role'     => $u->role?->slug ?? '',
                'label'    => "{$u->name} ({$dept})",
            ];
        })->values();

        // Ambil data absensi yang sudah dikunci oleh Super Admin untuk deteksi proteksi di preview
        $lockedMap = Attendance::where('is_locked', true)
            ->get(['user_id', 'date'])
            ->mapWithKeys(function ($att) {
                $d = $att->date instanceof Carbon ? $att->date->toDateString() : explode(' ', (string) $att->date)[0];
                return [((int) $att->user_id) . '_' . $d => true];
            })
            ->all();

        $currentEmployee = null;
        $parsedRows      = [];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // 1. Cek apakah seluruh baris kosong
            $isRowEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $isRowEmpty = false;
                    break;
                }
            }
            if ($isRowEmpty) {
                continue;
            }

            // 2. Abaikan baris panduan/contoh di template
            $rowAllText = strtolower(implode(' ', array_map('strval', $row)));
            if (
                str_contains($rowAllText, 'nama lengkap') ||
                str_contains($rowAllText, 'sesuai sistem') ||
                str_contains($rowAllText, 'cukup di baris pertama') ||
                str_contains($rowAllText, 'format:') ||
                str_contains($rowAllText, 'format :') ||
                str_contains($rowAllText, 'template import') ||
                str_contains($rowAllText, 'dd/mm/yyyy') ||
                str_contains($rowAllText, 'yyyy-mm-dd') ||
                str_contains($rowAllText, 'hh:mm')
            ) {
                continue;
            }

            // 3. Cek Nama Karyawan di baris ini
            $cellNama = isset($row[$colNama]) ? trim((string) $row[$colNama]) : '';
            if (!empty($cellNama)) {
                $matched = $findUser($cellNama);
                if ($matched instanceof User) {
                    $currentEmployee = $matched;
                } else {
                    $currentEmployee = null;
                }
            }

            $rawNameDisplay = !empty($cellNama) ? $cellNama : ($currentEmployee ? $currentEmployee->name : '');
            $matchedUserId  = $currentEmployee ? $currentEmployee->id : null;

            // 4. Parsing Tanggal
            $tanggalRaw = isset($row[$colTanggal]) ? trim((string) $row[$colTanggal]) : '';
            $targetDate = $this->parseImportDate($tanggalRaw);

            // 5. Parsing Jam Masuk & Pulang
            $checkInRaw       = isset($row[$colCheckIn]) ? trim((string) $row[$colCheckIn]) : '';
            $checkInFormatted = $this->parseImportTime($checkInRaw);

            $checkOutRaw       = isset($row[$colCheckOut]) ? trim((string) $row[$colCheckOut]) : '';
            $checkOutFormatted = $this->parseImportTime($checkOutRaw);

            // 6. Status
            $statusRaw     = isset($row[$colStatus]) ? trim((string) $row[$colStatus]) : '';
            $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
            $status        = 'Hadir';
            if (!empty($statusRaw)) {
                foreach ($validStatuses as $vs) {
                    if (strcasecmp($statusRaw, $vs) === 0) {
                        $status = $vs;
                        break;
                    }
                }
            }

            // 7. Catatan
            $noteRaw = isset($row[$colCatatan]) && !empty(trim((string) $row[$colCatatan]))
                ? trim((string) $row[$colCatatan])
                : 'Import Massal Absensi';

            $isRowLocked = false;
            if ($matchedUserId && $targetDate) {
                $isRowLocked = isset($lockedMap[((int) $matchedUserId) . '_' . $targetDate]);
            }

            // Evaluasi validitas baris
            $errors = [];
            if (empty($matchedUserId)) {
                $errors[] = !empty($rawNameDisplay) 
                    ? "Karyawan '{$rawNameDisplay}' tidak terdaftar" 
                    : "Nama karyawan belum dipilih";
            }
            if (empty($targetDate)) {
                $errors[] = !empty($tanggalRaw) 
                    ? "Tanggal '{$tanggalRaw}' tidak valid" 
                    : "Tanggal kosong";
            }
            if (empty($checkInFormatted) && empty($checkOutFormatted) && !in_array($status, ['Izin', 'Sakit', 'Cuti'])) {
                $errors[] = "Jam masuk dan jam pulang keduanya kosong";
            }

            $parsedRows[] = [
                'id'                => $i,
                'row_number'        => $i + 1,
                'raw_name'          => $rawNameDisplay,
                'user_id'           => $matchedUserId,
                'date'              => $targetDate ?: ($tanggalRaw ?: date('Y-m-d')),
                'check_in'          => $checkInFormatted ? substr($checkInFormatted, 0, 5) : '',
                'check_out'         => $checkOutFormatted ? substr($checkOutFormatted, 0, 5) : '',
                'status'            => $status,
                'note'              => $noteRaw,
                'is_locked'         => $isRowLocked,
                'is_valid'          => empty($errors),
                'error_message'     => !empty($errors) ? implode('; ', $errors) : null,
            ];
        }

        $validCount   = count(array_filter($parsedRows, fn($r) => $r['is_valid']));
        $invalidCount = count($parsedRows) - $validCount;

        return response()->json([
            'success'       => true,
            'total'         => count($parsedRows),
            'valid_count'   => $validCount,
            'invalid_count' => $invalidCount,
            'rows'          => $parsedRows,
            'employees'     => $employeeList,
        ]);
    }

    /**
     * Konfirmasi dan simpan data absensi hasil edit preview ke database
     */
    public function importConfirm(Request $request): JsonResponse
    {
        $rowsData = $request->input('rows');
        if (!is_array($rowsData) || empty($rowsData)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data absensi yang dikirim untuk di-import.',
            ], 422);
        }

        $importedCount      = 0;
        $skippedLockedCount = 0;
        $importedDates      = [];
        $createdIds         = [];
        $updatedSnapshots   = [];
        $users = User::with(['role', 'division'])->get()->keyBy('id');
        $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];

        DB::beginTransaction();
        try {
            foreach ($rowsData as $row) {
                $userId = isset($row['user_id']) ? (int) $row['user_id'] : null;
                if (!$userId || !isset($users[$userId])) {
                    continue; // Skip jika karyawan tidak valid
                }

                $employee = $users[$userId];

                $targetDate = $this->parseImportDate($row['date'] ?? null);
                if (!$targetDate) {
                    continue; // Skip jika tanggal tidak valid
                }

                // Proteksi Kunci: Jangan timpa data yang sudah diverifikasi / dikunci Super Admin
                $existing = Attendance::where('user_id', $employee->id)
                    ->whereDate('date', $targetDate)
                    ->first();

                if ($existing && $existing->is_locked) {
                    $skippedLockedCount++;
                    continue; // Dilindungi, lewati baris ini
                }

                // Snapshot data sebelum ditimpa (untuk fitur Undo / Rollback)
                if ($existing) {
                    $updatedSnapshots[] = [
                        'id'   => $existing->id,
                        'data' => $existing->only([
                            'check_in', 'check_out', 'status', 'lateness_minutes',
                            'is_pulang_cepat', 'note', 'lat', 'long',
                            'check_in_method', 'check_out_method'
                        ]),
                    ];
                }

                // Auto-Remember: Jika nama di Excel adalah nama panggilan / alias baru, simpan ke database
                $rawName = trim((string) ($row['raw_name'] ?? ''));
                if (!empty($rawName) && strcasecmp($rawName, $employee->name) !== 0 && strlen($rawName) >= 2) {
                    $currentNickname = (string) ($employee->nickname ?? '');
                    $existingAliases = array_map('trim', array_map('strtolower', preg_split('/[,;|]/', $currentNickname)));
                    if (!in_array(strtolower($rawName), $existingAliases, true)) {
                        $newNickname = empty($currentNickname) ? $rawName : ($currentNickname . ', ' . $rawName);
                        $employee->update(['nickname' => $newNickname]);
                        $employee->nickname = $newNickname; // sync in-memory
                    }
                }

                $checkInFormatted  = $this->parseImportTime($row['check_in'] ?? null);
                $checkOutFormatted = $this->parseImportTime($row['check_out'] ?? null);

                $statusRaw = trim((string) ($row['status'] ?? 'Hadir'));
                $status    = 'Hadir';
                foreach ($validStatuses as $vs) {
                    if (strcasecmp($statusRaw, $vs) === 0) {
                        $status = $vs;
                        break;
                    }
                }

                // Jika jam masuk & pulang kosong dan bukan izin/sakit/cuti, skip
                if (empty($checkInFormatted) && empty($checkOutFormatted) && !in_array($status, ['Izin', 'Sakit', 'Cuti'])) {
                    continue;
                }

                $noteRaw = !empty(trim((string) ($row['note'] ?? '')))
                    ? trim((string) $row['note'])
                    : 'Import Massal Absensi';

                // Hitung Keterlambatan
                $latenessMinutes = 0;
                if ($checkInFormatted && !in_array($status, ['Izin', 'Sakit', 'Cuti'])) {
                    $userDivision     = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
                    $isLiveStreaming  = str_contains($userDivision, 'live streaming');
                    $isSalesMarketing = str_contains($userDivision, 'sales marketing');

                    if (!$isLiveStreaming && !$isSalesMarketing) {
                        $checkInTime   = Carbon::parse($targetDate . ' ' . $checkInFormatted);
                        $lateThreshold = Carbon::parse($targetDate . ' 10:00:00');
                        if ($checkInTime->greaterThanOrEqualTo($lateThreshold)) {
                            $latenessMinutes = (int) $lateThreshold->diffInMinutes($checkInTime);
                            $status          = 'Terlambat';
                        }
                    }
                }

                // Hitung Is Pulang Cepat
                $isPulangCepat = false;
                if ($checkOutFormatted && $checkInFormatted) {
                    $userDivision  = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
                    $checkInTime   = Carbon::parse($targetDate . ' ' . $checkInFormatted);
                    $checkOutTime  = Carbon::parse($targetDate . ' ' . $checkOutFormatted);
                    $minutesWorked = (int) $checkInTime->diffInMinutes($checkOutTime);

                    if ($employee->role && $employee->role->slug === 'karyawan_ramayana') {
                        $isPulangCepat = $minutesWorked < (7 * 60);
                    } elseif (str_contains($userDivision, 'gudang')) {
                        $isPulangCepat = $checkOutFormatted < '18:00:00';
                    } else {
                        $isPulangCepat = $minutesWorked < (8 * 60);
                    }
                }

                $record = Attendance::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'date'    => $targetDate,
                    ],
                    [
                        'check_in'         => $checkInFormatted,
                        'check_out'        => $checkOutFormatted,
                        'status'           => $status,
                        'lateness_minutes' => $latenessMinutes,
                        'is_pulang_cepat'  => $isPulangCepat,
                        'note'             => $noteRaw,
                        'lat'              => 0,
                        'long'             => 0,
                        'check_in_method'  => 'manual',
                        'check_out_method' => 'manual',
                    ]
                );

                if (!$existing && $record) {
                    $createdIds[] = $record->id;
                }

                $importedDates[] = $targetDate;
                $importedCount++;
            }

            // Simpan Riwayat Batch Import untuk Fitur Undo / Rollback
            if ($importedCount > 0) {
                \App\Models\AttendanceImportBatch::create([
                    'user_id'           => Auth::id(),
                    'file_name'         => $request->input('file_name') ?: 'Import Excel Absensi',
                    'imported_count'    => $importedCount,
                    'status'            => 'active',
                    'created_ids'       => $createdIds,
                    'updated_snapshots' => $updatedSnapshots,
                ]);
            }

            DB::commit();

            $firstImportedDate  = null;
            $latestImportedDate = null;
            $importedMonth      = null;

            if (!empty($importedDates)) {
                sort($importedDates);
                $firstImportedDate  = reset($importedDates);
                $latestImportedDate = end($importedDates);
                $importedMonth      = Carbon::parse($latestImportedDate)->format('Y-m');
                session(['imported_month' => $importedMonth]);
            }

            $message = "✅ Berhasil meng-import {$importedCount} data absensi.";
            if ($skippedLockedCount > 0) {
                $message .= " ({$skippedLockedCount} data dilindungi & dilewati karena berstatus terkunci oleh Super Admin).";
            }

            return response()->json([
                'success'        => true,
                'message'        => $message,
                'count'          => $importedCount,
                'skipped_locked' => $skippedLockedCount,
                'target_date'    => $firstImportedDate,
                'imported_month' => $importedMonth,
                'redirect_url'   => $firstImportedDate
                    ? route('super-admin.attendance.index', ['date' => $firstImportedDate])
                    : route('super-admin.attendance.index'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data absensi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil daftar riwayat batch import untuk modal Undo / Rollback
     */
    public function importBatches(): JsonResponse
    {
        $batches = \App\Models\AttendanceImportBatch::with(['user', 'revertedByUser'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'file_name'      => $b->file_name ?: 'Import Excel Absensi',
                    'imported_count' => $b->imported_count,
                    'status'         => $b->status,
                    'created_at'     => $b->created_at ? $b->created_at->translatedFormat('d M Y, H:i') : '-',
                    'user_name'      => $b->user?->name ?? 'Super Admin',
                    'reverted_at'    => $b->reverted_at ? $b->reverted_at->translatedFormat('d M Y, H:i') : null,
                    'reverted_by'    => $b->revertedByUser?->name,
                    'can_revert'     => $b->status === 'active',
                ];
            });

        return response()->json([
            'success' => true,
            'batches' => $batches,
        ]);
    }

    /**
     * Membatalkan / Rollback hasil batch import (Undo)
     */
    public function revertImportBatch(\App\Models\AttendanceImportBatch $batch): JsonResponse
    {
        if ($batch->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Batch import ini sudah pernah dibatalkan sebelumnya.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $deletedCount  = 0;
            $restoredCount = 0;

            // 1. Hapus data yang dibuat baru pada batch ini
            if (!empty($batch->created_ids)) {
                $deletedCount = Attendance::whereIn('id', $batch->created_ids)->delete();
            }

            // 2. Pulihkan data yang sempat tertimpa ke nilai sebelumnya
            if (!empty($batch->updated_snapshots)) {
                foreach ($batch->updated_snapshots as $snap) {
                    if (isset($snap['id']) && !empty($snap['data'])) {
                        Attendance::where('id', $snap['id'])->update($snap['data']);
                        $restoredCount++;
                    }
                }
            }

            $batch->update([
                'status'      => 'reverted',
                'reverted_at' => now(),
                'reverted_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "✅ Berhasil membatalkan import: {$deletedCount} data baru dihapus, {$restoredCount} data lama dipulihkan.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import data absensi massal dari Excel / CSV (Direct fallback)
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $ext  = $file->getClientOriginalExtension();

        try {
            $rows = \App\Services\ExcelImportReader::readRows($path, $ext);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel/CSV: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'File Excel/CSV kosong atau tidak memiliki data.');
        }

        // Deteksi kolom dari baris header
        $headerRowIndex = null;
        $colNama        = null;
        $colTanggal     = null;
        $colCheckIn     = null;
        $colCheckOut    = null;
        $colStatus      = null;
        $colCatatan     = null;

        foreach ($rows as $index => $row) {
            $rowStr = implode(' ', array_map('strtolower', array_map('strval', $row)));
            // Lewati jika ini baris judul banner
            if (str_contains($rowStr, 'template import')) {
                continue;
            }

            if (str_contains($rowStr, 'nama') || str_contains($rowStr, 'karyawan') || str_contains($rowStr, 'tanggal') || str_contains($rowStr, 'masuk')) {
                $headerRowIndex = $index;
                foreach ($row as $colIdx => $val) {
                    $v = strtolower(trim((string) $val));
                    if (str_contains($v, 'nama') || str_contains($v, 'karyawan')) $colNama = $colIdx;
                    elseif (str_contains($v, 'tanggal') || str_contains($v, 'date')) $colTanggal = $colIdx;
                    elseif (str_contains($v, 'masuk') || str_contains($v, 'check_in') || str_contains($v, 'check in')) $colCheckIn = $colIdx;
                    elseif (str_contains($v, 'pulang') || str_contains($v, 'check_out') || str_contains($v, 'check out')) $colCheckOut = $colIdx;
                    elseif (str_contains($v, 'status')) $colStatus = $colIdx;
                    elseif (str_contains($v, 'catatan') || str_contains($v, 'note')) $colCatatan = $colIdx;
                }
                break;
            }
        }

        if ($headerRowIndex === null) $headerRowIndex = 0;
        if ($colNama === null) $colNama = 0;
        if ($colTanggal === null) $colTanggal = 1;
        if ($colCheckIn === null) $colCheckIn = 2;
        if ($colCheckOut === null) $colCheckOut = 3;
        if ($colStatus === null) $colStatus = 4;
        if ($colCatatan === null) $colCatatan = 5;

        // Ambil seluruh karyawan untuk pencocokan nama/username/email/alias
        $users   = User::with(['role', 'division'])->get();
        $userMap = [];
        $aliasMap = [];
        foreach ($users as $u) {
            $userMap[strtolower(trim((string) $u->name))] = $u;
            if (!empty($u->username)) {
                $userMap[strtolower(trim((string) $u->username))] = $u;
            }
            if (!empty($u->email)) {
                $userMap[strtolower(trim((string) $u->email))] = $u;
            }
            if (!empty($u->nickname)) {
                $aliases = preg_split('/[,;|]/', strtolower(trim((string) $u->nickname)));
                foreach ($aliases as $alias) {
                    $aliasClean = trim($alias);
                    if (!empty($aliasClean)) {
                        $aliasMap[$aliasClean] = $u;
                    }
                }
            }
        }

        // Fungsi pencocokan user cerdas (alias, kata panggilan, dan nama lengkap)
        $findUser = function (string $nameQuery) use ($userMap, $aliasMap, $users) {
            $clean = strtolower(preg_replace('/\s+/', ' ', trim($nameQuery)));
            if (empty($clean)) {
                return null;
            }

            // Exact match nama/email/username
            if (isset($userMap[$clean])) {
                return $userMap[$clean];
            }

            // Exact match alias/nickname
            if (isset($aliasMap[$clean])) {
                return $aliasMap[$clean];
            }

            foreach ($aliasMap as $aliasKey => $u) {
                if ($aliasKey === $clean || str_starts_with($clean, $aliasKey . ' ') || str_contains($clean, $aliasKey)) {
                    return $u;
                }
            }

            // Word token match (misal: "Rio" dicocokkan ke "Muhammad Rio Pratama")
            $candidates = [];
            foreach ($users as $u) {
                $uName = strtolower(trim((string) $u->name));
                $uWords = array_filter(explode(' ', $uName), fn($w) => strlen($w) >= 2);
                $isRamayana = ($u->role?->slug === 'karyawan_ramayana') || str_contains(strtolower($u->division?->name ?? ''), 'ramayana');

                if (in_array($clean, $uWords, true)) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 100 : 80];
                    continue;
                }

                if ($uName === $clean || str_starts_with($uName, $clean . ' ')) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 95 : 75];
                    continue;
                }

                if (str_contains($uName, $clean) && strlen($clean) >= 3) {
                    $candidates[] = ['user' => $u, 'score' => $isRamayana ? 70 : 50];
                }
            }

            if (!empty($candidates)) {
                usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
                return $candidates[0]['user'];
            }

            return null;
        };

        $currentEmployee    = null;
        $importedCount      = 0;
        $skippedCount       = 0;
        $skippedLockedCount = 0;
        $skippedDetails     = [];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // 1. Cek apakah seluruh baris kosong
            $isRowEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $isRowEmpty = false;
                    break;
                }
            }
            if ($isRowEmpty) {
                continue;
            }

            // 2. Abaikan baris panduan/contoh di template
            $rowAllText = strtolower(implode(' ', array_map('strval', $row)));
            if (
                str_contains($rowAllText, 'nama lengkap') ||
                str_contains($rowAllText, 'sesuai sistem') ||
                str_contains($rowAllText, 'cukup di baris pertama') ||
                str_contains($rowAllText, 'format:') ||
                str_contains($rowAllText, 'format :') ||
                str_contains($rowAllText, 'template import') ||
                str_contains($rowAllText, 'dd/mm/yyyy') ||
                str_contains($rowAllText, 'yyyy-mm-dd') ||
                str_contains($rowAllText, 'hh:mm')
            ) {
                continue;
            }

            // 3. Cek Nama Karyawan di baris ini
            $cellNama = isset($row[$colNama]) ? trim((string) $row[$colNama]) : '';

            if (!empty($cellNama)) {
                $matched = $findUser($cellNama);
                if ($matched instanceof User) {
                    $currentEmployee = $matched;
                    // Auto-remember alias jika beda dengan nama asli
                    if (strcasecmp($cellNama, $matched->name) !== 0 && strlen($cellNama) >= 2) {
                        $currNick = (string) ($matched->nickname ?? '');
                        $existAliases = array_map('trim', array_map('strtolower', preg_split('/[,;|]/', $currNick)));
                        if (!in_array(strtolower($cellNama), $existAliases, true)) {
                            $newNick = empty($currNick) ? $cellNama : ($currNick . ', ' . $cellNama);
                            $matched->update(['nickname' => $newNick]);
                            $matched->nickname = $newNick;
                        }
                    }
                } else {
                    $currentEmployee = false; // Tandai nama tidak ditemukan
                    $skippedCount++;
                    if (count($skippedDetails) < 5) {
                        $skippedDetails[] = "Baris " . ($i + 1) . " ('$cellNama' tidak terdaftar)";
                    }
                }
            }

            // Jika belum ada nama karyawan yang valid, lewati baris ini
            if (!$currentEmployee instanceof User) {
                if ($currentEmployee === false && empty($cellNama)) {
                    $skippedCount++;
                }
                continue;
            }

            $employee = $currentEmployee;

            // 4. Parsing Tanggal (Mendukung DD/MM/YYYY, YYYY-MM-DD, Excel Date Serial)
            $tanggalRaw = isset($row[$colTanggal]) ? trim((string) $row[$colTanggal]) : '';
            $targetDate = $this->parseImportDate($tanggalRaw);

            if (empty($targetDate)) {
                $skippedCount++;
                if (count($skippedDetails) < 5) {
                    $skippedDetails[] = "Baris " . ($i + 1) . " (Tanggal '$tanggalRaw' tidak valid)";
                }
                continue;
            }

            // Proteksi Kunci: Jangan timpa data yang sudah diverifikasi / dikunci Super Admin
            $existing = Attendance::where('user_id', $employee->id)
                ->whereDate('date', $targetDate)
                ->first();

            if ($existing && $existing->is_locked) {
                $skippedLockedCount++;
                continue; // Dilindungi, jangan ditimpa
            }

            // 5. Parsing Jam Masuk (Mendukung HH:MM, HH,MM koma, HH.MM titik, Serial Excel)
            $checkInRaw       = isset($row[$colCheckIn]) ? trim((string) $row[$colCheckIn]) : '';
            $checkInFormatted = $this->parseImportTime($checkInRaw);

            // 6. Parsing Jam Pulang
            $checkOutRaw       = isset($row[$colCheckOut]) ? trim((string) $row[$colCheckOut]) : '';
            $checkOutFormatted = $this->parseImportTime($checkOutRaw);

            // Jika jam masuk dan jam pulang keduanya kosong, lewati
            if (empty($checkInFormatted) && empty($checkOutFormatted)) {
                continue;
            }

            // 7. Status — Jika kolom Status kosong, otomatis = 'Hadir'
            $statusRaw     = isset($row[$colStatus]) ? trim((string) $row[$colStatus]) : '';
            $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
            $status        = 'Hadir'; // Default: Hadir
            if (!empty($statusRaw)) {
                foreach ($validStatuses as $vs) {
                    if (strcasecmp($statusRaw, $vs) === 0) {
                        $status = $vs;
                        break;
                    }
                }
            }

            // 8. Catatan
            $noteRaw = isset($row[$colCatatan]) && !empty(trim((string) $row[$colCatatan])) 
                ? trim((string) $row[$colCatatan]) 
                : 'Import Massal Absensi';

            // 9. Hitung Keterlambatan
            $latenessMinutes = 0;
            if ($checkInFormatted && !in_array($status, ['Izin', 'Sakit', 'Cuti'])) {
                $userDivision     = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
                $isLiveStreaming  = str_contains($userDivision, 'live streaming');
                $isSalesMarketing = str_contains($userDivision, 'sales marketing');

                if (!$isLiveStreaming && !$isSalesMarketing) {
                    $checkInTime   = Carbon::parse($targetDate . ' ' . $checkInFormatted);
                    $lateThreshold = Carbon::parse($targetDate . ' 10:00:00');
                    if ($checkInTime->greaterThanOrEqualTo($lateThreshold)) {
                        $latenessMinutes = (int) $lateThreshold->diffInMinutes($checkInTime);
                        $status          = 'Terlambat';
                    }
                }
            }

            // 10. Hitung Is Pulang Cepat
            $isPulangCepat = false;
            if ($checkOutFormatted && $checkInFormatted) {
                $userDivision  = $employee->division ? strtolower(trim((string) $employee->division->name)) : '';
                $checkInTime   = Carbon::parse($targetDate . ' ' . $checkInFormatted);
                $checkOutTime  = Carbon::parse($targetDate . ' ' . $checkOutFormatted);
                $minutesWorked = (int) $checkInTime->diffInMinutes($checkOutTime);

                if ($employee->role && $employee->role->slug === 'karyawan_ramayana') {
                    $isPulangCepat = $minutesWorked < (7 * 60);
                } elseif (str_contains($userDivision, 'gudang')) {
                    $isPulangCepat = $checkOutFormatted < '18:00:00';
                } else {
                    $isPulangCepat = $minutesWorked < (8 * 60);
                }
            }

            Attendance::updateOrCreate(
                [
                    'user_id' => $employee->id,
                    'date'    => $targetDate,
                ],
                [
                    'check_in'         => $checkInFormatted,
                    'check_out'        => $checkOutFormatted,
                    'status'           => $status,
                    'lateness_minutes' => $latenessMinutes,
                    'is_pulang_cepat'  => $isPulangCepat,
                    'note'             => $noteRaw,
                    'lat'              => 0,
                    'long'             => 0,
                    'check_in_method'  => 'manual',
                    'check_out_method' => 'manual',
                ]
            );

                $importedDates[] = $targetDate;
                $importedCount++;
        }

        $msg = "✅ Berhasil meng-import {$importedCount} data absensi.";
        if ($skippedLockedCount > 0) {
            $msg .= " ({$skippedLockedCount} data dilindungi & dilewati karena berstatus terkunci oleh Super Admin).";
        }
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris dilewati: " . implode(', ', $skippedDetails) . ")";
        }

        // Simpan bulan import terakhir ke session agar halaman rekap otomatis menampilkan bulan yang baru di-import
        $firstImportedDate = null;
        if (!empty($importedDates)) {
            sort($importedDates);
            $firstImportedDate  = reset($importedDates);
            $latestImportedDate = end($importedDates);
            session(['imported_month' => Carbon::parse($latestImportedDate)->format('Y-m')]);
        }

        if ($firstImportedDate) {
            return redirect()->route('super-admin.attendance.index', ['date' => $firstImportedDate])->with('success', $msg);
        }

        return back()->with('success', $msg);
    }
}