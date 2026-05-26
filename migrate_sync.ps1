# =========================================================
# SCRIPT SINKRONISASI MIGRATION DATABASE (LOKAL -> LIVE)
# =========================================================

Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host " MEMULAI MIGRASI DATABASE DI INFINITYFREE" -ForegroundColor Yellow
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""

$liveUrl = "http://absensirecord.fwh.is/run-migrations"

Write-Host "Menghubungi server live untuk menjalankan migrasi..." -ForegroundColor White
Write-Host "URL: $liveUrl" -ForegroundColor Gray

try {
    $response = Invoke-WebRequest -Uri $liveUrl -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host ""
        Write-Host "✅ Sukses! $($response.Content)" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "❌ Gagal! Server merespon dengan status: $($response.StatusCode)" -ForegroundColor Red
    }
} catch {
    Write-Host ""
    Write-Host "❌ Terjadi kesalahan saat menghubungi server live: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Proses selesai." -ForegroundColor Green
Write-Host "Silakan tekan tombol apa saja untuk keluar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
