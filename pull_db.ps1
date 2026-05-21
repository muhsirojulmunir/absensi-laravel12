$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localWorkspace = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12"

Write-Host "Mendownload database terbaru dari server InfinityFree ke komputer lokal..."
Write-Host "MOHON TUNGGU..."

$uri = [URI]"ftp://$ftpHost/htdocs/database/database.sqlite"
$localFilePath = "$localWorkspace\database\database.sqlite"
$backupDir = "$localWorkspace\database_backups"

if (!(Test-Path -Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

if (Test-Path -Path $localFilePath) {
    $timestamp = (Get-Date).ToString("yyyy-MM-dd_HH-mm-ss")
    $backupLocalFile = "$backupDir\lokal-sebelum-pull-$timestamp.sqlite"
    Copy-Item -Path $localFilePath -Destination $backupLocalFile
    Write-Host "✅ Database lokal Anda diamankan dulu ke: $backupLocalFile" -ForegroundColor Cyan
}

try {
    $ftp = [System.Net.FtpWebRequest]::Create($uri)
    $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $ftp.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $ftp.UseBinary = $true
    $ftp.UsePassive = $true
    
    $response = $ftp.GetResponse()
    $responseStream = $response.GetResponseStream()
    
    $fileStream = New-Object System.IO.FileStream($localFilePath, [System.IO.FileMode]::Create)
    $responseStream.CopyTo($fileStream)
    
    $fileStream.Close()
    $responseStream.Close()
    $response.Close()
    
    Write-Host ""
    Write-Host "SUKSES! Database lokal Anda sekarang sudah sinkron dengan data server live!" -ForegroundColor Green
    Write-Host "(Semua user baru dan data absen dari server sekarang sudah ada di laptop Anda)" -ForegroundColor Yellow
} catch {
    Write-Host ""
    Write-Error "GAGAL MENDOWNLOAD DATABASE! Error: $_"
}

Write-Host ""
Write-Host "Tekan Enter untuk menutup jendela ini..."
Read-Host
