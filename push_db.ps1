$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localWorkspace = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12"

$localDbPath = "$localWorkspace\database\database.sqlite"
$remoteDbPath = "/htdocs/database/database.sqlite"

Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host " MEMULAI UPLOAD DATABASE KE INFINITYFREE" -ForegroundColor Yellow
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Mengunggah database lokal (database.sqlite) ke server live..." -ForegroundColor White
Write-Host "Mohon tunggu, ini mungkin membutuhkan beberapa detik..." -ForegroundColor Gray

try {
    $uri = [URI]"ftp://$ftpHost$remoteDbPath"
    $ftp = [System.Net.FtpWebRequest]::Create($uri)
    $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $ftp.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $ftp.UseBinary = $true
    $ftp.UsePassive = $true
    
    $content = [System.IO.File]::ReadAllBytes($localDbPath)
    $ftp.ContentLength = $content.Length
    
    $rs = $ftp.GetRequestStream()
    $rs.Write($content, 0, $content.Length)
    $rs.Close()
    $rs.Dispose()
    
    $response = $ftp.GetResponse()
    $response.Close()
    $response.Dispose()
    
    Write-Host ""
    Write-Host "✅ SUKSES! Database berhasil diunggah ke server InfinityFree." -ForegroundColor Green
} catch {
    Write-Host ""
    Write-Error "❌ GAGAL mengunggah database. Error: $_"
}

Write-Host ""
Write-Host "Tekan tombol apa saja untuk keluar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
