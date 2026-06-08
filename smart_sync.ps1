$ftpHost = "ftpupload.net"
$ftpUser = "if0_41950452"
$ftpPass = "bFZSutp8ov1w"
$localWorkspace = "e:\Folder_Pekerjaan\Record\Koding Absensi\absensi-laravel12"
$stateFile = "$localWorkspace\sync_state.json"

# Memuat riwayat sinkronisasi sebelumnya
$state = @{}
if (Test-Path $stateFile) {
    try {
        $jsonContent = Get-Content $stateFile -Raw | ConvertFrom-Json
        foreach ($prop in $jsonContent.psobject.properties) {
            $state[$prop.Name] = $prop.Value
        }
    } catch {
        $state = @{}
    }
}

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
            $response.Dispose()
        } catch {
            # Abaikan jika folder sudah ada
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
        $rs.Dispose()
        
        $response = $ftp.GetResponse()
        $response.Close()
        $response.Dispose()
        return $true
    } catch {
        Write-Error "Gagal mengunggah $LocalPath. Error: $_"
        return $false
    }
}

$uploadedCount = 0

function Sync-Directory {
    param ([string]$CurrentLocalDir, [string]$CurrentRemoteDir)
    
    # Buat direktori jika ada file yang perlu diunggah (akan dibuat otomatis di dalam iterasi)
    $dirCreated = $false
    
    $items = Get-ChildItem -Path $CurrentLocalDir
    foreach ($item in $items) {
        $name = $item.Name
        
        if ($item.PSIsContainer) {
            # Folder yang di-blacklist (jangan di-sync!)
            if ($CurrentLocalDir -eq $localWorkspace) {
                if ($name -eq ".git" -or $name -eq "vendor" -or $name -eq "node_modules" -or $name -eq "storage" -or $name -eq "database_backups" -or $name -eq "bootstrap") {
                    continue
                }
            }
            Sync-Directory -CurrentLocalDir $item.FullName -CurrentRemoteDir "$CurrentRemoteDir/$name"
        } else {
            # File yang di-blacklist
            if ($name -eq "vendor.zip" -or $name -match "\.ps1$" -or $name -eq "extract.php" -or $name -eq "sync_state.json" -or $name -match "\.sqlite$") {
                continue
            }
            
            # Cek apakah file berubah (berdasarkan waktu modifikasi terakhir)
            $lastModified = $item.LastWriteTimeUtc.Ticks.ToString()
            $fileKey = $item.FullName.Replace($localWorkspace, "")
            
            if (!$state.ContainsKey($fileKey) -or $state[$fileKey] -ne $lastModified) {
                # File baru atau ada perubahan!
                if (!$dirCreated) {
                    Create-Dir-FTP -RemotePath $CurrentRemoteDir
                    $dirCreated = $true
                }
                
                Write-Host "Mengunggah file yang berubah: $name..." -ForegroundColor Cyan
                $success = Upload-File-FTP -LocalPath $item.FullName -RemotePath "$CurrentRemoteDir/$name"
                
                if ($success) {
                    $state[$fileKey] = $lastModified
                    $script:uploadedCount++
                }
            }
        }
    }
}

Write-Host "Memulai Smart Sync (Hanya mengunggah file yang berubah)..." -ForegroundColor Yellow
Sync-Directory -CurrentLocalDir $localWorkspace -CurrentRemoteDir "/htdocs"

# Simpan riwayat sinkronisasi baru
$state | ConvertTo-Json | Set-Content $stateFile

Write-Host ""
if ($uploadedCount -eq 0) {
    Write-Host "✅ SELESAI! Tidak ada file kodingan yang berubah. Sistem Up-to-date!" -ForegroundColor Green
} else {
    Write-Host "✅ SELESAI! Berhasil mengunggah $uploadedCount file yang baru/diubah." -ForegroundColor Green
}
