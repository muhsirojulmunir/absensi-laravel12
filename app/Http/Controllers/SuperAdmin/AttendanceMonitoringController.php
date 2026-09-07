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

        // 2. Format YYYY-MM-DD atau YYYY/MM/DD atau YYYY.MM.DD
        if (preg_match('/^(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // 3. Format DD-MM-YYYY atau DD/MM/YYYY atau DD.MM.YYYY (Standar Indonesia)
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // 4. Format DD-MM-YY atau DD/MM/YY atau DD.MM.YY
        if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{2})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', 2000 + (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // 5. Fallback via Carbon
        try {
            return Carbon::parse($raw)->toDateString();
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
     * Import data absensi massal dari Excel / CSV
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

        // Ambil seluruh karyawan untuk pencocokan nama/username/email
        $users   = User::with(['role', 'division'])->get();
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

        // Fungsi pencocokan user yang toleran terhadap spasi / awalan nama
        $findUser = function (string $nameQuery) use ($userMap, $users) {
            $clean = strtolower(preg_replace('/\s+/', ' ', trim($nameQuery)));
            if (empty($clean)) {
                return null;
            }

            // Exact match
            if (isset($userMap[$clean])) {
                return $userMap[$clean];
            }

            // Loose match (awalan nama atau kecocokan tanpa spasi ganda)
            foreach ($users as $u) {
                $uName = strtolower(trim((string) $u->name));
                if ($uName === $clean || str_starts_with($uName, $clean . ' ') || str_contains($uName, $clean)) {
                    return $u;
                }
            }

            return null;
        };

        $currentEmployee = null;
        $importedCount   = 0;
        $skippedCount    = 0;
        $skippedDetails  = [];

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
            $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit'];
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

            $importedCount++;
        }

        $msg = "✅ Berhasil meng-import {$importedCount} data absensi.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris dilewati karena karyawan tidak terdaftar atau format tanggal tidak valid: " . implode(', ', $skippedDetails) . ")";
        }

        return back()->with('success', $msg);
    }
}