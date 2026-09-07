<?php

namespace App\Http\Controllers\PIC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Show attendance report form and results.
     */
    public function index(Request $request)
    {
        $currentRouteName = $request->route() ? $request->route()->getName() : null;
        if ($currentRouteName && in_array($currentRouteName, ['pic_ramayana.reports.index', 'pic.reports.index', 'super-admin.attendance.index'])) {
            $reportRouteName = $currentRouteName;
        } elseif (Auth::user()?->role?->slug === 'pic_ramayana') {
            $reportRouteName = 'pic_ramayana.reports.index';
        } else {
            $reportRouteName = 'pic.reports.index';
        }

        $targetRoles = ['karyawan'];
        if ($reportRouteName === 'pic_ramayana.reports.index' || Auth::user()?->role?->slug === 'pic_ramayana') {
            $targetRoles = ['karyawan_ramayana'];
        } elseif (Auth::user()?->role?->slug === 'super-admin' || strtolower(Auth::user()?->username ?? '') === 'superadmin1') {
            $targetRoles = ['karyawan', 'karyawan_ramayana'];
        }

        // Get list of employees (users with target roles)
        $employees = User::whereHas('role', function ($q) use ($targetRoles) {
            $q->whereIn('slug', $targetRoles);
        })->where('is_active', true)
        ->with(['role', 'division', 'location'])
        ->get()
        ->sortBy(function($user) {
            $roleOrder = $user->role->slug === 'karyawan' ? 1 : 2;
            return $roleOrder . '_' . strtolower($user->name);
        });

        // Determine selected employee and month (default current month or recently imported month)
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', session('imported_month', Carbon::now()->format('Y-m')));
        $start = Carbon::parse($month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();
        $today = Carbon::today();
        $effectiveEnd = $end->lt($today) ? $end : $today->copy()->endOfDay();

        // Ambil hari libur dalam bulan ini
        $holidays = Holiday::whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get();

        $report = null;
        $allReports = collect();

        if ($employeeId) {
            $attendances = Attendance::query()
                ->where('user_id', $employeeId)
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->orderBy('date', 'asc')
                ->get();

            $leaves = LeaveRequest::query()
                ->where('user_id', $employeeId)
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->whereIn('status', ['approved', 'pending'])
                ->orderBy('start_date', 'asc')
                ->get();

            $empObj = User::with(['role', 'division', 'location'])->whereKey($employeeId)->first();
            $metrics = $this->calculateMetrics($empObj, $attendances, $leaves, $start, $effectiveEnd, $holidays);

            $report = [
                'attendances'       => $attendances,
                'leaves'            => $leaves,
                'summary'           => $metrics['summary'],
                'masuk_list'        => $metrics['masuk_list'],
                'lupa_absen_list'   => $metrics['lupa_absen_list'],
                'tidak_hadir_list'  => $metrics['tidak_hadir_list'],
                'employee'          => $empObj,
                'month'             => $month,
            ];
        } else {
            // Pre-load attendances and leaves untuk efisiensi
            $allAttendances = Attendance::whereIn('user_id', $employees->pluck('id'))
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString())
                ->orderBy('date', 'asc')
                ->get()
                ->groupBy('user_id');

            $allLeaves = LeaveRequest::whereIn('user_id', $employees->pluck('id'))
                ->whereDate('start_date', '<=', $end->toDateString())
                ->whereDate('end_date', '>=', $start->toDateString())
                ->whereIn('status', ['approved', 'pending'])
                ->orderBy('start_date', 'asc')
                ->get()
                ->groupBy('user_id');

            $allReports = $employees->map(function ($employee) use ($start, $effectiveEnd, $holidays, $allAttendances, $allLeaves) {
                $attendances = $allAttendances->get($employee->id, collect());
                $leaves = $allLeaves->get($employee->id, collect());

                $metrics = $this->calculateMetrics($employee, $attendances, $leaves, $start, $effectiveEnd, $holidays);

                return [
                    'employee'          => $employee,
                    'attendances'       => $attendances,
                    'leaves'            => $leaves,
                    'summary'           => $metrics['summary'],
                    'masuk_list'        => $metrics['masuk_list'],
                    'lupa_absen_list'   => $metrics['lupa_absen_list'],
                    'tidak_hadir_list'  => $metrics['tidak_hadir_list'],
                ];
            })->sortByDesc(function ($item) {
                return [
                    $item['summary']['total_masuk'],
                    $item['summary']['total_present'],
                ];
            })->values();
        }

        return view('pic.reports.index', compact('employees', 'report', 'allReports', 'employeeId', 'month', 'reportRouteName'));
    }

    /**
     * Hitung akumulasi kehadiran, hari libur/tidak hadir, dan lupa absen
     */
    private function calculateMetrics(User $employee, $attendances, $leaves, Carbon $start, Carbon $effectiveEnd, $holidays): array
    {
        $divisionName  = strtolower(trim($employee->division?->name ?? ''));
        $isStaffKantor = str_contains($divisionName, 'staff kantor');
        $isRamayana    = $employee->role?->slug === 'karyawan_ramayana';

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

        $holidayByDate = $holidays->filter(function ($h) use ($employee) {
            return is_null($h->division_id) || $h->division_id == $employee->division_id;
        })->keyBy(fn($h) => Carbon::parse($h->date)->toDateString());

        $totalHadir = 0;
        $totalLate  = 0;
        $totalLeave = 0;
        $totalSick  = 0;

        $masukList      = [];
        $lupaAbsenList  = [];
        $tidakHadirList = [];

        $cursor = $start->copy();
        while ($cursor->lte($effectiveEnd)) {
            $dateStr       = $cursor->toDateString();
            $dayName       = $cursor->locale('id')->translatedFormat('l');
            $formattedDate = $cursor->locale('id')->translatedFormat('d M Y');
            $label         = "{$dayName}, {$formattedDate}";

            $att       = $attByDate->get($dateStr);
            $leave     = $leaveDates[$dateStr] ?? null;
            $holiday   = $holidayByDate->get($dateStr);
            $isWeekend = $cursor->isWeekend();

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
                    $totalSick++;
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => 'Sakit' . ($att->note ? ': ' . $att->note : ''),
                        'is_holiday' => false,
                    ];
                } elseif (in_array($attStatus, ['Izin', 'Cuti'])) {
                    $totalLeave++;
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => ($attStatus === 'Cuti' ? 'Cuti' : 'Izin') . ($att->note ? ': ' . $att->note : ''),
                        'is_holiday' => false,
                    ];
                } elseif (in_array($attStatus, ['Alpa', 'Tidak Hadir'])) {
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => 'Tidak Hadir' . ($att->note ? ': ' . $att->note : ''),
                        'is_holiday' => false,
                    ];
                } else {
                    // Absensi masuk / hadir / terlambat riil
                    if ($att->status === 'Hadir') {
                        $totalHadir++;
                    } elseif ($att->status === 'Terlambat') {
                        $totalLate++;
                    }

                    $checkInRaw  = $att->check_in;
                    $checkOutRaw = $att->check_out;
                    $checkIn     = $checkInRaw ? Carbon::parse($checkInRaw)->format('H.i') : null;
                    $checkOut    = $checkOutRaw ? Carbon::parse($checkOutRaw)->format('H.i') : null;

                    $isLupaMasuk  = empty($checkInRaw) && !empty($checkOutRaw);
                    $isLupaPulang = (!empty($checkInRaw) && empty($checkOutRaw) && !$cursor->isToday());

                    // 1. Kolom TENGAH: Lupa Absen (Gabungan masuk dan pulang)
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

                    // 2. Kolom KIRI: Masuk (Tetap dihitung masuk, tapi ditulis catatan lupa absen)
                    $lupaTag = null;
                    if ($isLupaMasuk) {
                        $lupaTag = 'Lupa Absen Masuk';
                    } elseif ($isLupaPulang) {
                        $lupaTag = 'Lupa Absen Pulang';
                    }

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
                $type = $leave->type;
                if ($type === 'Sakit') {
                    $totalSick++;
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => 'Sakit' . ($leave->reason ? ': ' . $leave->reason : ''),
                        'is_holiday' => false,
                    ];
                } elseif (in_array($type, ['Libur', 'Libur (Day Off)'])) {
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => 'Libur (Day Off)',
                        'is_holiday' => true,
                    ];
                } else {
                    $totalLeave++;
                    $tidakHadirList[] = [
                        'label'      => $label,
                        'keterangan' => 'Izin: ' . ($leave->reason ?? $type),
                        'is_holiday' => false,
                    ];
                }
            } else {
                // Tidak ada absensi dan tidak ada izin
                $keterangan   = 'Tidak Absen';
                $isHolidayDay = false;

                if ($holiday) {
                    $keterangan   = 'Libur: ' . ($holiday->description ?? 'Hari Libur Nasional');
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

        return [
            'summary' => [
                'total_present'            => $totalHadir,
                'total_late'               => $totalLate,
                'total_masuk'              => count($masukList),
                'total_off_days'           => count($tidakHadirList),
                'total_lupa_absen'         => count($lupaAbsenList),
                'total_leave'              => $totalLeave,
                'total_sick'               => $totalSick,
                'total_attendance_records' => $attendances->count(),
            ],
            'masuk_list'       => $masukList,
            'lupa_absen_list'  => $lupaAbsenList,
            'tidak_hadir_list' => $tidakHadirList,
        ];
    }
}
