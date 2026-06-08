<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendSmartNotification extends Command
{
    protected $signature = 'notify:smart-attendance {--test}';
    protected $description = 'Kirim notifikasi pengingat absen otomatis via Firebase Cloud Messaging';

    public function handle()
    {
        $this->info('🤖 Memulai Smart Notification...');
        $now = Carbon::now();
        $today = Carbon::today();

        $firebase = new FirebaseService();

        // Ambil semua karyawan yang punya fcm_token
        $users = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->with(['division', 'attendances' => function ($query) use ($today) {
                $query->whereDate('date', $today);
            }])
            ->whereHas('role', function ($q) {
                $q->where('slug', 'karyawan');
            })
            ->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada karyawan dengan FCM token yang terdaftar.');
            return 0;
        }

        $this->info("Ditemukan {$users->count()} karyawan dengan FCM token.");

        $sentCount = 0;

        foreach ($users as $user) {
            $divisionName = strtolower(trim($user->division->name ?? ''));
            $isLiveStreaming = str_contains($divisionName, 'live streaming');
            $todayAttendance = $user->attendances->first();

            // ================================================================
            // LOGIKA STAFF KANTOR (Bukan Live Streaming)
            // ================================================================
            if (!$isLiveStreaming) {
                // Pengingat MASUK: Jam 07:45 - 08:30, belum Clock In
                $isTimeMasuk = ($now->format('H:i') >= '07:45' && $now->format('H:i') <= '08:30');
                if ($isTimeMasuk || $this->option('test')) {
                    if (!$todayAttendance) {
                        $this->info("📤 Mengirim pengingat Clock In ke {$user->name} (Staff Kantor)");

                        $message = "Halo {$user->name}, jangan lupa untuk melakukan absen masuk (Clock In) pagi ini. Selamat bekerja dan semoga harimu menyenangkan!";

                        if ($firebase->sendNotification($user->fcm_token, '⏰ Pengingat Absen Masuk', $message)) {
                            $sentCount++;
                        }
                        continue; // Skip next checks for this user if test triggered this
                    }
                }

                // Pengingat PULANG: Jam 17:00 - 17:30, sudah Clock In tapi belum Clock Out
                $isTimePulang = ($now->format('H:i') >= '17:00' && $now->format('H:i') <= '17:30');
                if ($isTimePulang || $this->option('test')) {
                    if ($todayAttendance && !$todayAttendance->check_out) {
                        $this->info("📤 Mengirim pengingat Clock Out ke {$user->name} (Staff Kantor)");

                        $message = "Halo {$user->name}, waktu kerja hari ini sudah selesai (17:00 WIB). Terima kasih atas kerja kerasnya dan jangan lupa absen pulang (Clock Out).";

                        if ($firebase->sendNotification($user->fcm_token, '🏠 Waktunya Pulang!', $message)) {
                            $sentCount++;
                        }
                    }
                }
            }

            // ================================================================
            // LOGIKA LIVE STREAMING (8 Jam Kerja Fleksibel)
            // ================================================================
            if ($isLiveStreaming) {
                if ($todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out) {
                    $clockIn = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $today->format('Y-m-d') . ' ' . $todayAttendance->check_in
                    );
                    $hoursWorked = $clockIn->diffInMinutes($now);

                    // Kirim notif setelah 8 jam bekerja (antara menit 480-510)
                    if (($hoursWorked >= 480 && $hoursWorked <= 510) || $this->option('test')) {
                        $this->info("📤 Mengirim pengingat 8 jam ke {$user->name} (Live Streaming)");

                        $message = "Halo {$user->name}, kamu sudah bekerja selama 8 jam lho! Waktunya istirahat dan jangan lupa untuk melakukan absen pulang (Clock Out) ya.";

                        if ($firebase->sendNotification($user->fcm_token, '⏰ Sudah 8 Jam Bekerja!', $message)) {
                            $sentCount++;
                        }
                    }
                }
            }
        }

        $this->info("✅ Selesai! Total notifikasi terkirim: {$sentCount}");
        return 0;
    }
}
