$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localWorkspace = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12"

function Create-Dir-FTP {
    param ([string]$RemotePath)
    $folders = $RemotePath.TrimStart("/").Split("/")
    $currentPath = ""
    foreach ($folder in $folders) {
        $currentPath += "/$folder"
        $uri = [URI]"ftp://$ftpHost$currentPath"
        try {
            $ftp = [System.Net.FtpWebRequest]::Create($uri)
            $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $ftp.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $response = $ftp.GetResponse()
            $response.Close()
        } catch {
        }
    }
}

function Upload-File-FTP {
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
        Write-Host "Uploaded: $RemotePath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "Failed: $RemotePath - $_" -ForegroundColor Red
        return $false
    }
}

function Sync-Specific-Folder {
    param ([string]$LocalDir, [string]$RemoteDir)
    
    if (!(Test-Path $LocalDir)) { return }
    
    Create-Dir-FTP -RemotePath $RemoteDir
    
    $items = Get-ChildItem -Path $LocalDir
    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            Sync-Specific-Folder -LocalDir $item.FullName -RemoteDir "$RemoteDir/$($item.Name)"
        } else {
            Upload-File-FTP -LocalPath $item.FullName -RemotePath "$RemoteDir/$($item.Name)"
        }
    }
}

Write-Host "Uploading Composer Autoloader..." -ForegroundColor Cyan
Sync-Specific-Folder -LocalDir "$localWorkspace\vendor\composer" -RemoteDir "/htdocs/vendor/composer"

Write-Host "Uploading Shuchkin SimpleXLSX package..." -ForegroundColor Cyan
Sync-Specific-Folder -LocalDir "$localWorkspace\vendor\shuchkin" -RemoteDir "/htdocs/vendor/shuchkin"

Write-Host "Upload Complete!" -ForegroundColor Yellow
