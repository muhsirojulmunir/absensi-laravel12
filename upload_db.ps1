$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$LocalPath = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12\database\database.sqlite"
$RemotePath = "/jmnmatrix.rf.gd/htdocs/database/database.sqlite"

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
    $rs.Dispose()
    
    $response = $ftp.GetResponse()
    $response.Close()
    $response.Dispose()
    Write-Host "Database berhasil diupload!" -ForegroundColor Green
} catch {
    Write-Error "Gagal mengunggah database. Error: $_"
}
