# Script khusus upload vendor pagination views ke InfinityFree
$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localPaginationDir = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12\resources\views\vendor\pagination"
$remotePaginationDir = "/htdocs/resources/views/vendor/pagination"

function Create-FTP-Dir {
    param ([string]$RemotePath)
    $uri = [URI]"ftp://$ftpHost$RemotePath"
    try {
        $ftp = [System.Net.FtpWebRequest]::Create($uri)
        $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $ftp.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $response = $ftp.GetResponse()
        $response.Close()
    } catch { }
}

function Upload-FTP-File {
    param ([string]$LocalPath, [string]$RemotePath)
    $uri = [URI]"ftp://$ftpHost$RemotePath"
    try {
        $ftp = [System.Net.FtpWebRequest]::Create($uri)
        $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $ftp.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftp.UseBinary = $true
        $ftp.UsePassive = $true
        $content = [System.IO.File]::ReadAllBytes($LocalPath)
        $ftp.ContentLength = $content.Length
        $rs = $ftp.GetRequestStream()
        $rs.Write($content, 0, $content.Length)
        $rs.Close()
        $response = $ftp.GetResponse()
        $response.Close()
        return $true
    } catch {
        Write-Host "  GAGAL: $_" -ForegroundColor Red
        return $false
    }
}

Write-Host "Membuat folder pagination di server..." -ForegroundColor Yellow
Create-FTP-Dir -RemotePath "/htdocs/resources"
Create-FTP-Dir -RemotePath "/htdocs/resources/views"
Create-FTP-Dir -RemotePath "/htdocs/resources/views/vendor"
Create-FTP-Dir -RemotePath "/htdocs/resources/views/vendor/pagination"

Write-Host "Mengunggah file pagination..." -ForegroundColor Yellow
$count = 0
foreach ($file in Get-ChildItem -Path $localPaginationDir -File) {
    Write-Host "  -> $($file.Name)" -ForegroundColor Cyan
    $success = Upload-FTP-File -LocalPath $file.FullName -RemotePath "$remotePaginationDir/$($file.Name)"
    if ($success) { $count++ }
}

Write-Host ""
Write-Host "Selesai! $count file pagination berhasil diunggah." -ForegroundColor Green
Write-Host "Silakan refresh halaman di InfinityFree untuk mengecek hasilnya." -ForegroundColor Green
