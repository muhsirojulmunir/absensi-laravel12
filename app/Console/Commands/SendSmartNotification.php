<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendSmartNotification extends Command
{
    protected $signature = 'notify:smart-attendance {--test} {--user=} {--type=}';
    protected $description = 'Kirim notifikasi pengingat absen otomatis via Firebase Cloud Messaging';

    public function handle()
    {
        $this->info('🤖 Memulai Smart Notification...');
        $now = Carbon::now();
        $today = Carbon::today();
        $isTest = $this->option('test');
        $filterUser = $this->option('user'); // opsional: filter nama/id user
        $filterType = $this->option('type'); // opsional: 'masuk', 'pulang', '8jam'

        $firebase = new FirebaseService();

        // Ambil semua karyawan yang punya fcm_token
        $query = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->with(['division', 'attendances' => function ($q) use ($today) {
                $q->whereDate('date', $today);
            }])
            ->whereHas('role', function ($q) {
                $q->where('slug', 'karyawan');
            });

        if ($filterUser) {
            $query->where(function ($q) use ($filterUser) {
                $q->where('name', 'like', "%{$filterUser}%")
                  ->orWhere('id', $filterUser);
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada karyawan dengan FCM token yang terdaftar.');
            return 0;
        }

        $this->info("Ditemukan {$users->count()} karyawan dengan FCM token.");
        $this->info("Waktu sekarang: " . $now->format('H:i:s') . " WIB");

        $sentCount = 0;

        foreach ($users as $user) {
            $divisionName = strtolower(trim($user->division->name ?? ''));
            $isLiveStreaming = str_contains($divisionName, 'live streaming');
            $todayAttendance = $user->attendances->first();

            // ================================================================
            // LOGIKA STAFF KANTOR (Bukan Live Streaming)
            // ================================================================
            if (!$isLiveStreaming) {
                $currentTime = $now->format('H:i');

                // --- Pengingat MASUK: Jam 07:45 - 08:30, belum Clock In ---
                $isTimeMasuk = ($currentTime >= '07:45' && $currentTime <= '08:30');
                $shouldSendMasuk = $isTimeMasuk || ($isTest && (!$filterType || $filterType === 'masuk'));

                if ($shouldSendMasuk && !$todayAttendance) {
                    // Anti-spam: kirim maksimal 1x per 5 menit per user
                    $cacheKeyMasuk = "notif_masuk_{$user->id}_" . $now->format('YmdHi');
                    // Bulatkan ke 5 menit terdekat untuk caching
                    $roundedMinute = floor($now->minute / 5) * 5;
                    $cacheKeyMasuk = "notif_masuk_{$user->id}_" . $now->format('Ymdh') . "_{$roundedMinute}";

                    if (!Cache::has($cacheKeyMasuk) || $isTest) {
                        $this->info("📤 Mengirim pengingat Clock In ke {$user->name} (Staff Kantor)");

                        $message = "Halo {$user->name}, jangan lupa untuk melakukan absen masuk (Clock In) pagi ini. Selamat bekerja dan semoga harimu menyenangkan! 🌅";

                        if ($firebase->sendNotification($user->fcm_token, '⏰ Pengingat Absen Masuk', $message)) {
                            $sentCount++;
                            Cache::put($cacheKeyMasuk, true, now()->addMinutes(5));
                            Log::info("Notif masuk terkirim ke: {$user->name}");
                        }
                    } else {
                        $this->line("⏭ Notif masuk {$user->name} sudah dikirim dalam 5 menit ini, skip.");
                    }
                }

                // --- Pengingat PULANG: Jam 17:00 - 17:15, sudah Clock In tapi belum Clock Out ---
                $isTimePulang = ($currentTime >= '17:00' && $currentTime <= '17:15');
                $shouldSendPulang = $isTimePulang || ($isTest && (!$filterType || $filterType === 'pulang'));

                if ($shouldSendPulang && $todayAttendance && !$todayAttendance->check_out) {
                    // Anti-spam: kirim maksimal 1x per 5 menit per user
                    $roundedMinute = floor($now->minute / 5) * 5;
                    $cacheKeyPulang = "notif_pulang_{$user->id}_" . $now->format('Ymdh') . "_{$roundedMinute}";

                    if (!Cache::has($cacheKeyPulang) || $isTest) {
                        $this->info("📤 Mengirim pengingat Clock Out ke {$user->name} (Staff Kantor)");

                        $message = "Halo {$user->name}, waktu kerja hari ini sudah selesai (17:00 WIB). Terima kasih atas kerja kerasnya hari ini! Jangan lupa absen pulang (Clock Out). 🏠";

                        if ($firebase->sendNotification($user->fcm_token, '🏠 Waktunya Pulang!', $message)) {
                            $sentCount++;
                            Cache::put($cacheKeyPulang, true, now()->addMinutes(5));
                            Log::info("Notif pulang terkirim ke: {$user->name}");
                        }
                    } else {
                        $this->line("⏭ Notif pulang {$user->name} sudah dikirim dalam 5 menit ini, skip.");
                    }
                }
            }

            // ================================================================
            // LOGIKA LIVE STREAMING (8 Jam Kerja Fleksibel)
            // ================================================================
            if ($isLiveStreaming) {
                $attendance = $todayAttendance;

                // Cek shift malam kemarin (jika masih pagi hari berikutnya)
                if (!$attendance || !$attendance->check_in) {
                    $yesterdayAttendance = Attendance::where('user_id', $user->id)
                        ->whereDate('date', Carbon::yesterday())
                        ->whereNotNull('check_in')
                        ->whereNull('check_out')
                        ->first();
                    if ($yesterdayAttendance) {
                        $attendance = $yesterdayAttendance;
                    }
                }

                if ($attendance && $attendance->check_in && !$attendance->check_out) {
                    $attendanceDate = Carbon::parse($attendance->date);

                    // Parse waktu check_in - handle format 'H:i:s' atau 'H:i'
                    try {
                        $clockIn = Carbon::createFromFormat(
                            'Y-m-d H:i:s',
                            $attendanceDate->format('Y-m-d') . ' ' . $attendance->check_in
                        );
                    } catch (\Exception $e) {
                        $clockIn = Carbon::createFromFormat(
                            'Y-m-d H:i',
                            $attendanceDate->format('Y-m-d') . ' ' . substr($attendance->check_in, 0, 5)
                        );
                    }

                    $minutesWorked = $clockIn->diffInMinutes($now);

                    $this->line("ℹ️  {$user->name} sudah bekerja " . round($minutesWorked / 60, 1) . " jam (check_in: {$attendance->check_in})");

                    // Kirim notif antara menit 480-510 (8 jam - 8.5 jam)
                    $shouldSend8Jam = ($minutesWorked >= 480 && $minutesWorked <= 510)
                        || ($isTest && (!$filterType || $filterType === '8jam'));

                    if ($shouldSend8Jam) {
                        $cacheKey8Jam = "notif_8jam_{$user->id}_" . $attendanceDate->toDateString();

                        if (!Cache::has($cacheKey8Jam) || $isTest) {
                            $this->info("📤 Mengirim pengingat 8 jam ke {$user->name} (Live Streaming)");

                            $message = "Halo {$user->name}, kamu sudah bekerja selama 8 jam lho! Waktunya istirahat dan jangan lupa untuk melakukan absen pulang (Clock Out) ya. 💪";

                            if ($firebase->sendNotification($user->fcm_token, '⏰ Sudah 8 Jam Bekerja!', $message)) {
                                $sentCount++;
                                Cache::put($cacheKey8Jam, true, now()->endOfDay());
                                Log::info("Notif 8 jam terkirim ke: {$user->name}");
                            }
                        } else {
                            $this->line("⏭ Notif 8 jam {$user->name} sudah dikirim hari ini, skip.");
                        }
                    }
                } else {
                    if ($isTest) {
                        $this->line("ℹ️  {$user->name} tidak ada sesi kerja aktif hari ini.");
                    }
                }
            }
        }

        $this->info("✅ Selesai! Total notifikasi terkirim: {$sentCount}");
        return 0;
    }
}
