# =========================================================
# SCRIPT SINKRONISASI DATABASE (LOKAL -> LIVE)
# =========================================================

Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host " MEMULAI SINKRONISASI DATABASE KE INFINITYFREE" -ForegroundColor Yellow
Write-Host " (Hanya Akun & Biodata, Data Absensi Tetap Aman)" -ForegroundColor Yellow
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""

# Cek apakah PHP tersedia
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "Error: PHP tidak ditemukan di sistem Anda." -ForegroundColor Red
    Exit 1
}

# Jalankan perintah artisan untuk sinkronisasi
php artisan sync:database

Write-Host ""
Write-Host "Proses selesai." -ForegroundColor Green
Write-Host "Silakan tekan tombol apa saja untuk keluar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
