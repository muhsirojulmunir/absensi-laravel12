@echo off
REM ================================================================
REM Setup Windows Task Scheduler untuk Laravel Scheduler
REM Jalankan script ini sebagai Administrator (klik kanan > Run as Admin)
REM ================================================================

SET PROJECT_PATH=e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12
SET PHP_PATH=C:\xampp\php\php.exe
SET TASK_NAME=LaravelScheduler_AbsensiRecord

echo ============================================================
echo  Setup Laravel Scheduler - Windows Task Scheduler
echo ============================================================
echo.
echo Project: %PROJECT_PATH%
echo PHP:     %PHP_PATH%
echo Task:    %TASK_NAME%
echo.

REM Hapus task lama jika ada
schtasks /delete /tn "%TASK_NAME%" /f 2>NUL

REM Buat task baru: jalankan schedule:run setiap 1 menit
REM Ini akan memicu notify:smart-attendance setiap 5 menit (sesuai jadwal)
schtasks /create ^
  /tn "%TASK_NAME%" ^
  /tr "\"%PHP_PATH%\" \"%PROJECT_PATH%\artisan\" schedule:run" ^
  /sc MINUTE /mo 1 ^
  /ru SYSTEM ^
  /rl HIGHEST ^
  /f

IF %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Task Scheduler berhasil dibuat!
    echo      Laravel Scheduler akan berjalan setiap 1 menit.
    echo      Notifikasi absensi akan dikirim setiap 5 menit (sesuai jadwal).
    echo.
    schtasks /query /tn "%TASK_NAME%" /fo LIST
) ELSE (
    echo.
    echo [ERROR] Gagal membuat Task Scheduler. 
    echo         Pastikan menjalankan script ini sebagai Administrator!
)

echo.
echo ============================================================
echo  Untuk memverifikasi: buka Task Scheduler dan cari task
echo  bernama: %TASK_NAME%
echo ============================================================
pause
