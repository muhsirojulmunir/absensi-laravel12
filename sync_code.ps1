$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localWorkspace = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12"

function Create-Dir-FTP {
    param (
        [string]$RemotePath
    )
    $uri = [URI]"ftp://$ftpHost$RemotePath"
    try {
        $ftp = [System.Net.FtpWebRequest]::Create($uri)
        $ftp.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $ftp.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $response = $ftp.GetResponse()
        $response.Close()
        $response.Dispose()
        Write-Host "Created FTP directory: $RemotePath"
    } catch {
        # Directory might already exist, swallow error
    }
}

function Upload-File-FTP {
    param (
        [string]$LocalPath,
        [string]$RemotePath
    )
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
        Write-Host "Uploaded: $RemotePath"
    } catch {
        Write-Error "Failed to upload $LocalPath. Error: $_"
    }
}

# Recursively scan and upload files/folders
function Sync-Directory {
    param (
        [string]$CurrentLocalDir,
        [string]$CurrentRemoteDir
    )
    
    # Ensure current remote directory exists
    Create-Dir-FTP -RemotePath $CurrentRemoteDir
    
    # Get all items in current directory
    $items = Get-ChildItem -Path $CurrentLocalDir
    foreach ($item in $items) {
        $name = $item.Name
        
        # Exclude directories we don't want to sync
        if ($item.PSIsContainer) {
            if ($name -eq ".git" -or $name -eq "vendor" -or $name -eq "node_modules" -or $name -eq "storage" -or $name -eq "database" -or $name -eq "database_backups") {
                continue
            }
            # Recursively sync subdirectories
            Sync-Directory -CurrentLocalDir $item.FullName -CurrentRemoteDir "$CurrentRemoteDir/$name"
        } else {
            # Exclude specific files
            if ($name -eq "vendor.zip" -or $name -eq "deploy.ps1" -or $name -eq "deploy_optimized.ps1" -or $name -eq "extract.php") {
                continue
            }
            # Upload file
            Upload-File-FTP -LocalPath $item.FullName -RemotePath "$CurrentRemoteDir/$name"
        }
    }
}

Write-Host "Starting code synchronization..."
Sync-Directory -CurrentLocalDir $localWorkspace -CurrentRemoteDir "/htdocs"
Write-Host "Code synchronization completed!"
