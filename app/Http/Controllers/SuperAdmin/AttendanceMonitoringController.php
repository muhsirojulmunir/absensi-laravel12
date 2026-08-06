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

        $attendances = Attendance::with(['user.division'])
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
            'note'            => $newNote,
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
     * Download template Excel/CSV import absensi massal
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_absen.csv"',
        ];

        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility
            fputcsv($file, ['Nama Karyawan', 'Tanggal (YYYY-MM-DD)', 'Jam Masuk (HH:MM)', 'Jam Pulang (HH:MM)', 'Status (Hadir/Terlambat/Izin/Sakit)', 'Catatan']);
            fputcsv($file, ['Contoh Nama Karyawan', date('Y-m-d'), '08:00', '17:00', 'Hadir', 'Absen Import Massal']);
            fclose($file);
        }, 'template_import_absen.csv', $headers);
    }

    /**
     * Import data absensi massal dari Excel / CSV
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        try {
            $rows = \App\Services\ExcelImportReader::readRows($path);
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

        // Ambil seluruh karyawan aktif untuk pencocokan nama/username/email
        $users   = User::all();
        $userMap = [];
        foreach ($users as $u) {
            $userMap[strtolower(trim((string) $u->name))] = $u;
            if (!empty($u->username)) {
                $userMap[strtolower(trim((string) $u->username))] = $u;
            }
            if (!empty($u->email)) {
                $userMap[strtolower(trim((string) $u->email))] = $u;
            }
        }

        $importedCount  = 0;
        $skippedCount   = 0;
        $skippedDetails = [];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $namaRaw = isset($row[$colNama]) ? trim((string) $row[$colNama]) : '';
            if (empty($namaRaw)) continue;

            $namaKey = strtolower($namaRaw);

            // LOGIKA UTAMA: Jika nama karyawan TIDAK ADA di sistem -> LEWATI (SKIP)
            if (!isset($userMap[$namaKey])) {
                $skippedCount++;
                if (count($skippedDetails) < 5) {
                    $skippedDetails[] = "Baris " . ($i + 1) . " ('$namaRaw')";
                }
                continue;
            }

            $employee = $userMap[$namaKey];

            // Parsing Tanggal
            $tanggalRaw = isset($row[$colTanggal]) ? trim((string) $row[$colTanggal]) : '';
            $targetDate = null;
            if (!empty($tanggalRaw)) {
                try {
                    if (is_numeric($tanggalRaw)) {
                        $targetDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalRaw))->toDateString();
                    } else {
                        $targetDate = Carbon::parse($tanggalRaw)->toDateString();
                    }
                } catch (\Throwable $e) {
                    $targetDate = now()->toDateString();
                }
            } else {
                $targetDate = now()->toDateString();
            }

            // Parsing Jam Masuk
            $checkInRaw       = isset($row[$colCheckIn]) ? trim((string) $row[$colCheckIn]) : '';
            $checkInFormatted = null;
            if (!empty($checkInRaw)) {
                if (is_numeric($checkInRaw)) {
                    $checkInFormatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($checkInRaw)->format('H:i:00');
                } else {
                    $checkInFormatted = date('H:i:00', strtotime($checkInRaw));
                }
            }

            // Parsing Jam Pulang
            $checkOutRaw       = isset($row[$colCheckOut]) ? trim((string) $row[$colCheckOut]) : '';
            $checkOutFormatted = null;
            if (!empty($checkOutRaw)) {
                if (is_numeric($checkOutRaw)) {
                    $checkOutFormatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($checkOutRaw)->format('H:i:00');
                } else {
                    $checkOutFormatted = date('H:i:00', strtotime($checkOutRaw));
                }
            }

            // Status
            $statusRaw     = isset($row[$colStatus]) ? trim((string) $row[$colStatus]) : '';
            $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit'];
            $status        = 'Hadir';
            foreach ($validStatuses as $vs) {
                if (strcasecmp($statusRaw, $vs) === 0) {
                    $status = $vs;
                    break;
                }
            }

            // Catatan
            $noteRaw = isset($row[$colCatatan]) && !empty(trim((string) $row[$colCatatan])) 
                ? trim((string) $row[$colCatatan]) 
                : 'Import Massal Absensi';

            // Hitung Keterlambatan
            $latenessMinutes = 0;
            if ($checkInFormatted && !in_array($status, ['Izin', 'Sakit'])) {
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

                if ($employee->role->slug === 'karyawan_ramayana') {
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
                ]
            );

            $importedCount++;
        }

        $msg = "✅ Berhasil meng-import {$importedCount} data absensi.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris dilewati karena karyawan tidak terdaftar di sistem: " . implode(', ', $skippedDetails) . ")";
        }

        return back()->with('success', $msg);
    }
};