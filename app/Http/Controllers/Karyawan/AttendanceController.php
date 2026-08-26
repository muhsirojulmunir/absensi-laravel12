<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Mulai jam berapa Karyawan Ramayana yang belum absen masuk hanya boleh
     * melakukan absen PULANG (mode "pulang saja").
     */
    private const JAM_MODE_PULANG_SAJA = 16;

    private function hasActiveLupaAbsenRequestOnDate($user, $date): bool
    {
        return $user->leaveRequests()
            ->where('type', 'Lupa Absen')
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', $date)
            ->exists();
    }

    /**
     * Jarak (meter, rumus Haversine) dari satu titik GPS ke titik lat/long lain.
     */
    private function jarakMeter(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371000; // radius bumi dalam meter
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lon2 - $lon1);
        $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
        return $r * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Hitung jarak terdekat dari posisi karyawan ke lokasi (toko) yang ditugaskan
     * kepadanya, sekaligus apakah dia berada dalam radius SALAH SATU lokasi itu.
     * Meniru persis logika di dashboard.blade.php (calculateDistance) supaya hasil
     * client-side dan server-side konsisten. Server hanya menegakkan aturan ini
     * untuk Karyawan Ramayana — staff kantor/live streamer tidak terpengaruh.
     *
     * @return array{distance: int|null, within: bool, nearestRadius: int|null}
     */
    private function hitungJarakTerdekat($user, $lat, $long): array
    {
        if ($lat === null || $long === null || $lat === '' || $long === '') {
            return ['distance' => null, 'within' => false, 'nearestRadius' => null];
        }

        $locations = $user->all_locations;
        if ($locations->isEmpty()) {
            return ['distance' => null, 'within' => false, 'nearestRadius' => null];
        }

        $minDistance = null;
        $within = false;
        $nearestRadius = null;

        foreach ($locations as $loc) {
            $d = $this->jarakMeter((float) $lat, (float) $long, (float) $loc->latitude, (float) $loc->longitude);
            if ($minDistance === null || $d < $minDistance) {
                $minDistance = $d;
                $nearestRadius = (int) $loc->radius;
            }
            if ($d <= (float) $loc->radius) {
                $within = true;
            }
        }

        return [
            'distance' => $minDistance !== null ? (int) round($minDistance) : null,
            'within' => $within,
            'nearestRadius' => $nearestRadius,
        ];
    }

    /**
     * Simpan foto bukti "Absen Manual" (selfie + area counter) ke disk.
     * Nama file mengandung user id, tipe (masuk/keluar), dan waktu server —
     * bukan waktu perangkat karyawan — supaya cap waktu tidak bisa dimanipulasi.
     */
    private function simpanFotoAbsenManual($file, int $userId, string $tipe): string
    {
        $dir = public_path('storage/absen-manual');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $fileName = 'absen-manual/' . $userId . '_' . $tipe . '_' . now()->format('Ymd_His') . '_' . uniqid() . '.jpg';
        $file->move($dir, basename($fileName));

        return $fileName;
    }

    public function index()
    {
        $userAttendances = Auth::user()->attendances()->get();
        
        $holidays = \App\Models\Holiday::query()->where(function ($query) {
            $query->whereNull('division_id')
                  ->orWhere('division_id', Auth::user()->division_id);
        })->get();

        $mergedAttendances = collect();
        $attendanceDates = [];

        foreach ($userAttendances as $att) {
            $mergedAttendances->push($att);
            $attendanceDates[] = \Carbon\Carbon::parse($att->date)->format('Y-m-d');
        }

        foreach ($holidays as $holiday) {
            $holidayDate = \Carbon\Carbon::parse($holiday->date)->format('Y-m-d');
            if (!in_array($holidayDate, $attendanceDates) && $holidayDate <= \Carbon\Carbon::today()->format('Y-m-d')) {
                $mergedAttendances->push((object)[
                    'date' => $holidayDate,
                    'check_in' => null,
                    'check_out' => null,
                    'status' => 'Libur',
                    'is_pulang_cepat' => false,
                    'note' => $holiday->description
                ]);
            }
        }

        $attendances = $mergedAttendances->sortByDesc(function($item) {
            return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
        })->values();

        $currentMonthStats = $attendances->filter(function($item) {
            return \Carbon\Carbon::parse($item->date)->isCurrentMonth();
        });

        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        return view('karyawan.attendance.index', compact('attendances', 'currentMonthStats', 'settings'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // ── Metode absen: 'otomatis' (tombol GPS) atau 'manual' (foto + cap waktu) ──
        // Absen Manual HANYA berlaku untuk Karyawan Ramayana. Kalau ada yang mencoba
        // mengirim method=manual dari role lain, paksa jadi 'otomatis' agar validasi
        // & perilaku staff kantor/live streamer tidak berubah sama sekali.
        $isManual = $request->input('method') === 'manual' && $user->role->slug === 'karyawan_ramayana';

        if ($isManual) {
            if (!$request->hasFile('photo')) {
                return response()->json(['success' => false, 'message' => 'Foto wajib diambil untuk Absen Manual.']);
            }
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'photo' => 'image|mimes:jpeg,jpg,png|max:6144',
            ]);
            if ($v->fails()) {
                return response()->json(['success' => false, 'message' => $v->errors()->first('photo')]);
            }
        } else {
            // Basic validation (jalur normal, tidak berubah dari sebelumnya)
            $request->validate([
                'lat' => 'required|numeric',
                'long' => 'required|numeric'
            ], [
                'lat.required' => 'Lokasi latitude diperlukan.',
                'long.required' => 'Lokasi longitude diperlukan.',
            ]);
        }

        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();
        $userDivision = $user->division ? strtolower(trim($user->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        if ($isLiveStreaming) {
            $overnightShift = $user->attendances()
                ->whereDate('date', \Carbon\Carbon::yesterday())
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->first();

            // Hanya blokir clock in jika belum lewat jam 08:30 pagi
            if ($overnightShift && $now->format('H:i') <= '08:30') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda masih memiliki shift kemarin yang belum Clock Out. Silakan Clock Out terlebih dahulu.'
                ]);
            }
        }

        // Check if already checked in today
        $attendance = $user->attendances()->whereDate('date', $today)->first();

        if ($attendance && $attendance->check_in) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock In hari ini.']);
        }

        // ── Mode "Pulang Saja" (khusus Karyawan Ramayana) ────────────────────
        // Mulai jam 16:00, karyawan yang belum absen masuk TIDAK boleh lagi absen
        // masuk — karena hampir pasti yang dimaksud adalah absen pulang (lupa absen
        // masuk di pagi hari). Ini mencegah data tercatat sebagai "masuk jam 19:00"
        // yang selama ini bikin absensi jadi tidak masuk akal.
        // Aturan ini TIDAK berlaku untuk staff kantor & live streamer.
        if ($user->role->slug === 'karyawan_ramayana' && (int) $now->format('H') >= self::JAM_MODE_PULANG_SAJA) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah lewat jam ' . self::JAM_MODE_PULANG_SAJA . ':00, absen masuk tidak bisa dilakukan. Jika Anda lupa absen masuk pagi tadi, silakan ajukan lewat menu Izin → Lupa Absen.',
            ]);
        }

        // ── Validasi radius di SERVER (khusus Karyawan Ramayana, metode otomatis) ──
        // Sebelumnya radius hanya dicek di JavaScript, jadi bisa diakali dengan
        // mengirim lat/long palsu langsung ke API. Sekarang server ikut menghitung
        // ulang jaraknya dan menolak jika di luar radius toko yang ditugaskan —
        // karyawan yang memang di luar radius diarahkan memakai Absen Manual.
        $jarakInfo = ['distance' => null, 'within' => true, 'nearestRadius' => null];
        if ($user->role->slug === 'karyawan_ramayana') {
            // lat/long boleh kosong untuk metode manual (GPS mungkin tidak tersedia);
            // kalau ada, tetap dihitung jaraknya untuk keperluan monitoring.
            $jarakInfo = $this->hitungJarakTerdekat($user, $request->lat, $request->long);

            if (!$isManual && !$jarakInfo['within']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda di luar radius toko (jarak ' . $jarakInfo['distance'] . 'm, maksimal ' . $jarakInfo['nearestRadius'] . 'm). Gunakan "Absen Manual" jika Anda memang sedang bertugas di sini.',
                ]);
            }
        }

        $photoPath = $isManual ? $this->simpanFotoAbsenManual($request->file('photo'), $user->id, 'masuk') : null;

        $dataAbsen = [
            'check_in' => $now->format('H:i:s'),
            'status' => 'Hadir',
            'is_pulang_cepat' => false,
            'lat' => $request->lat,
            'long' => $request->long,
            'check_in_method' => $isManual ? 'manual' : 'otomatis',
            'check_in_photo' => $photoPath,
            'check_in_distance_meters' => $jarakInfo['distance'],
        ];

        if ($attendance && !$attendance->check_in) {
            $attendance->update($dataAbsen);
        } else {
            $user->attendances()->create(array_merge($dataAbsen, ['date' => $today]));
        }

        return response()->json(['success' => true, 'message' => 'Clock In berhasil dicatat.']);
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $today = \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();

        // ── Metode absen: 'otomatis' (tombol GPS) atau 'manual' (foto + cap waktu) ──
        // Sama seperti store(): manual HANYA berlaku untuk Karyawan Ramayana.
        $isManual = $request->input('method') === 'manual' && $user->role->slug === 'karyawan_ramayana';

        if ($isManual) {
            if (!$request->hasFile('photo')) {
                return response()->json(['success' => false, 'message' => 'Foto wajib diambil untuk Absen Manual.']);
            }
            $v = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'photo' => 'image|mimes:jpeg,jpg,png|max:6144',
            ]);
            if ($v->fails()) {
                return response()->json(['success' => false, 'message' => $v->errors()->first('photo')]);
            }
        }

        $userDivision = $user->division ? strtolower(trim($user->division->name)) : '';
        $isLiveStreaming = str_contains($userDivision, 'live streaming');

        $attendance = $user->attendances()->whereDate('date', $today)->first();

        // Khusus Live Streaming: Cek shift malam kemarin (Batas jam 08:30 pagi)
        if ($isLiveStreaming && (!$attendance || !$attendance->check_in)) {
            if ($now->format('H:i') <= '08:30') {
                $overnightAttendance = $user->attendances()
                    ->whereDate('date', \Carbon\Carbon::yesterday())
                    ->whereNotNull('check_in')
                    ->whereNull('check_out')
                    ->first();
                
                if ($overnightAttendance) {
                    $attendance = $overnightAttendance;
                }
            }
        }

        if ($attendance && $attendance->check_out) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan Clock Out.']);
        }

        // ── Absen pulang tanpa absen masuk ───────────────────────────────────
        // Hanya diizinkan untuk Karyawan Ramayana dan mulai jam 16:00 (mode
        // "pulang saja"). Sebelum jam itu, absen pulang tetap mensyaratkan absen
        // masuk agar tidak ada absen pulang "liar" di pagi hari.
        $belumAbsenMasuk = !$attendance || !$attendance->check_in;

        if ($belumAbsenMasuk && !$isLiveStreaming) {
            if ($user->role->slug !== 'karyawan_ramayana') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan Clock In hari ini.',
                ]);
            }

            if ((int) $now->format('H') < self::JAM_MODE_PULANG_SAJA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan Clock In hari ini. Absen pulang tanpa absen masuk baru bisa dilakukan mulai jam ' . self::JAM_MODE_PULANG_SAJA . ':00.',
                ]);
            }
        }

        $isPulangCepat = false;

        if ($user->role->slug === 'karyawan_ramayana') {
            if ($attendance && $attendance->check_in) {
                $dateStr = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : explode(' ', (string)$attendance->date)[0];
                $clockInTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                $minutesWorked = $clockInTime->diffInMinutes($now);
                $isPulangCepat = $minutesWorked < (7 * 60);
            } else {
                $isPulangCepat = false;
            }
        } elseif (str_contains($userDivision, 'gudang')) {
            $isPulangCepat = $now->format('H:i') < '18:00';
        } else {
            if ($attendance && $attendance->check_in) {
                $dateStr = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : explode(' ', (string)$attendance->date)[0];
                $clockInTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                $minutesWorked = $clockInTime->diffInMinutes($now);
                $isPulangCepat = $minutesWorked < (8 * 60);
            } else {
                $isPulangCepat = $now->format('H:i') < '17:00'; // Default behavior if check_in is missing
            }
        }

        // ── Validasi radius di SERVER (khusus Karyawan Ramayana, metode otomatis) ──
        // Lihat catatan lengkap yang sama di store(). Absen Manual tidak terkena
        // aturan ini sama sekali.
        $jarakInfo = ['distance' => null, 'within' => true, 'nearestRadius' => null];
        if ($user->role->slug === 'karyawan_ramayana') {
            $jarakInfo = $this->hitungJarakTerdekat($user, $request->lat, $request->long);

            // Hanya tolak kalau lat/long memang terkirim dan terbukti di luar radius.
            // Kalau lat/long tidak ada sama sekali (kasus tak terduga), jangan blokir —
            // supaya tidak ada regresi dibanding perilaku checkout sebelumnya.
            if (!$isManual && $request->filled('lat') && $request->filled('long') && !$jarakInfo['within']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda di luar radius toko (jarak ' . $jarakInfo['distance'] . 'm, maksimal ' . $jarakInfo['nearestRadius'] . 'm). Gunakan "Absen Manual" jika Anda memang sedang bertugas di sini.',
                ]);
            }
        }

        $photoPath = $isManual ? $this->simpanFotoAbsenManual($request->file('photo'), $user->id, 'pulang') : null;

        $dataAbsen = [
            'check_out' => $now->format('H:i:s'),
            'is_pulang_cepat' => $isPulangCepat,
            'status' => 'Hadir',
            'check_out_method' => $isManual ? 'manual' : 'otomatis',
            'check_out_photo' => $photoPath,
            'check_out_distance_meters' => $jarakInfo['distance'],
        ];

        if ($attendance) {
            $attendance->update($dataAbsen);
        } else {
            $user->attendances()->create(array_merge($dataAbsen, [
                'date' => $today,
                'check_in' => null,
                'lat' => $request->lat ?? 0,
                'long' => $request->long ?? 0,
            ]));
        }

        return response()->json(['success' => true, 'message' => 'Clock Out berhasil dicatat.']);
    }
}
