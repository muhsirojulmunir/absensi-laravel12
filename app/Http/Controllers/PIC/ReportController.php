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
        $targetRoles = ['karyawan'];
        if (Auth::user()->role->slug === 'pic_ramayana') {
            $targetRoles = ['karyawan_ramayana'];
        } elseif (Auth::user()->role->slug === 'super-admin' || strtolower(Auth::user()->username) === 'superadmin1') {
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

        // Determine selected employee and month (default current month)
        $employeeId = $request->query('employee_id');
        $month = $request->query('month', Carbon::now()->format('Y-m'));
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
                'attendances' => $attendances,
                'leaves' => $leaves,
                'summary' => $metrics['summary'],
                'libur_details' => $metrics['libur_details'],
                'lupa_absen_masuk' => $metrics['lupa_absen_masuk'],
                'lupa_absen_pulang' => $metrics['lupa_absen_pulang'],
                'employee' => $empObj,
                'month' => $month,
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
                    'employee' => $employee,
                    'attendances' => $attendances,
                    'leaves' => $leaves,
                    'summary' => $metrics['summary'],
                    'libur_details' => $metrics['libur_details'],
                    'lupa_absen_masuk' => $metrics['lupa_absen_masuk'],
                    'lupa_absen_pulang' => $metrics['lupa_absen_pulang'],
                ];
            })->sortByDesc(function ($item) {
                return [
                    $item['summary']['total_masuk'],
                    $item['summary']['total_present'],
                ];
            })->values();
        }

        return view('pic.reports.index', compact('employees', 'report', 'allReports', 'employeeId', 'month'));
    }

    /**
     * Hitung akumulasi kehadiran, hari libur/tidak hadir, dan lupa absen
     */
    private function calculateMetrics(User $employee, $attendances, $leaves, Carbon $start, Carbon $effectiveEnd, $holidays): array
    {
        $divisionName = strtolower(trim($employee->division?->name ?? ''));
        $isStaffKantor = str_contains($divisionName, 'staff kantor');
        $isRamayana = $employee->role?->slug === 'karyawan_ramayana';

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

        $holidayByDate = $holidays->keyBy(fn($h) => Carbon::parse($h->date)->toDateString());

        $totalHadir = 0;
        $totalLate = 0;
        $totalLeave = 0;
        $totalSick = 0;

        $liburDetails = [];
        $lupaAbsenMasuk = [];
        $lupaAbsenPulang = [];

        $cursor = $start->copy();
        while ($cursor->lte($effectiveEnd)) {
            $dateStr = $cursor->toDateString();
            $dayName = $cursor->locale('id')->translatedFormat('l');
            $formattedDate = $cursor->locale('id')->translatedFormat('d M Y');
            $label = "{$dayName}, {$formattedDate}";

            $att = $attByDate->get($dateStr);
            $leave = $leaveDates[$dateStr] ?? null;
            $holiday = $holidayByDate->get($dateStr);
            $isWeekend = $cursor->isWeekend();

            if ($att) {
                if ($att->status === 'Hadir') {
                    $totalHadir++;
                } elseif ($att->status === 'Terlambat') {
                    $totalLate++;
                } elseif ($att->status === 'Sakit') {
                    $totalSick++;
                } elseif (in_array($att->status, ['Izin', 'Cuti'])) {
                    $totalLeave++;
                } elseif (str_starts_with((string)$att->status, 'Libur')) {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => $att->status,
                    ];
                }

                // Lupa Absen Masuk: ada absensi berstatus Hadir/Terlambat tetapi tidak ada jam check_in
                if (in_array($att->status, ['Hadir', 'Terlambat']) && empty($att->check_in)) {
                    $lupaAbsenMasuk[] = [
                        'label' => $label,
                        'detail' => $att->check_out ? 'Tidak ada jam masuk (Pulang: ' . Carbon::parse($att->check_out)->format('H:i') . ')' : 'Tidak ada jam masuk',
                    ];
                }

                // Lupa Absen Pulang: ada jam check_in tetapi check_out kosong (kecuali hari ini)
                if (in_array($att->status, ['Hadir', 'Terlambat']) && !empty($att->check_in) && empty($att->check_out)) {
                    if (!$cursor->isToday()) {
                        $lupaAbsenPulang[] = [
                            'label' => $label,
                            'detail' => 'Masuk jam ' . Carbon::parse($att->check_in)->format('H:i') . ', tidak ada jam pulang',
                        ];
                    }
                }
            } elseif ($leave) {
                $type = $leave->type;
                if ($type === 'Sakit') {
                    $totalSick++;
                } elseif (in_array($type, ['Libur', 'Libur (Day Off)'])) {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => 'Libur (Day Off)',
                    ];
                } else {
                    $totalLeave++;
                }
            } else {
                // Tidak ada absensi dan tidak ada izin
                if ($holiday) {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => 'Libur Nasional: ' . ($holiday->description ?? 'Hari Libur'),
                    ];
                } elseif ($isStaffKantor && $isWeekend) {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => 'Libur Akhir Pekan',
                    ];
                } elseif ($isRamayana && $cursor->isSunday()) {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => 'Libur Hari Minggu',
                    ];
                } else {
                    $liburDetails[] = [
                        'label' => $label,
                        'reason' => 'Tidak Hadir / Tanpa Keterangan',
                    ];
                }
            }

            $cursor->addDay();
        }

        $totalMasuk = $totalHadir + $totalLate;
        $totalLupaAbsen = count($lupaAbsenMasuk) + count($lupaAbsenPulang);

        return [
            'summary' => [
                'total_present' => $totalHadir,
                'total_late' => $totalLate,
                'total_masuk' => $totalMasuk,
                'total_off_days' => count($liburDetails),
                'total_lupa_absen' => $totalLupaAbsen,
                'total_leave' => $totalLeave,
                'total_sick' => $totalSick,
                'total_attendance_records' => $attendances->count(),
            ],
            'libur_details' => $liburDetails,
            'lupa_absen_masuk' => $lupaAbsenMasuk,
            'lupa_absen_pulang' => $lupaAbsenPulang,
        ];
    }
}
