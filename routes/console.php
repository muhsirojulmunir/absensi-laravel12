<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pengecekan pengingat absensi setiap 5 menit
// Staff kantor: pengingat masuk (07:45-08:30) dan pulang (17:00-17:15) setiap 5 menit
// Live streamer: pengingat checkout setelah 8 jam kerja, cek setiap 5 menit
Schedule::command('notify:smart-attendance')->everyFiveMinutes();
