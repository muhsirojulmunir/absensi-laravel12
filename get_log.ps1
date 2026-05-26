$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"

$uri = [URI]"ftp://$ftpHost/htdocs/storage/logs/laravel.log"
$localPath = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12\remote_laravel.log"

try { 
    $ftp = [System.Net.FtpWebRequest]::Create($uri)
    $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $ftp.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $ftp.UseBinary = $true
    
    $response = $ftp.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    
    [System.IO.File]::WriteAllText($localPath, $content)
    Write-Host "Log downloaded to $localPath"
} catch { 
    Write-Host "Error downloading log: $_" 
}
