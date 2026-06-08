<?php
/**
 * One-time installer: extracts vendor patch to the correct location.
 * Akses URL ini di browser: https://absensirecord.fwh.is/install_patch.php
 * Setelah berhasil, file ini akan menghapus dirinya sendiri.
 */

set_time_limit(120);

$zipPath = __DIR__ . '/patch_vendor.zip';
$extractTo = dirname(__DIR__) . '/vendor';

echo "<html><head><title>Patch Installer</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:600px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;} pre{background:#1e293b;padding:15px;border-radius:10px;overflow-x:auto;}
</style></head><body>";

echo "<h2>🔧 Vendor Patch Installer</h2>";

if (!file_exists($zipPath)) {
    echo "<p class='err'>❌ File patch_vendor.zip tidak ditemukan di folder public!</p>";
    echo "<p>Pastikan Anda sudah menjalankan smart_sync.ps1 terlebih dahulu.</p>";
    echo "</body></html>";
    exit;
}

echo "<p>📦 File zip ditemukan: " . number_format(filesize($zipPath) / 1024, 1) . " KB</p>";

// Create vendor dir if not exists
if (!is_dir($extractTo)) {
    mkdir($extractTo, 0755, true);
    echo "<p class='ok'>📁 Folder vendor dibuat.</p>";
}

$zip = new ZipArchive;
$res = $zip->open($zipPath);

if ($res === TRUE) {
    echo "<p>📂 Mengekstrak " . $zip->numFiles . " file...</p>";
    
    $extracted = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        
        // Target path: vendor/[filename]
        $targetPath = $extractTo . '/' . $filename;
        $targetDir = dirname($targetPath);
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Skip directories (they end with /)
        if (substr($filename, -1) === '/') continue;
        
        $content = $zip->getFromIndex($i);
        if ($content !== false) {
            file_put_contents($targetPath, $content);
            $extracted++;
        }
    }
    
    $zip->close();
    echo "<p class='ok'>✅ Berhasil mengekstrak $extracted file ke folder vendor!</p>";
    
    // Hapus zip setelah selesai
    unlink($zipPath);
    echo "<p class='ok'>🗑️ File zip dihapus.</p>";
    
    // Test: coba autoload
    echo "<h2>🧪 Verifikasi...</h2>";
    try {
        require_once $extractTo . '/autoload.php';
        if (class_exists('Shuchkin\SimpleXLSX')) {
            echo "<p class='ok'>✅ Class Shuchkin\SimpleXLSX DITEMUKAN! Patch berhasil 100%!</p>";
        } else {
            echo "<p class='err'>⚠️ Class belum terdeteksi, tapi file sudah terekstrak. Coba refresh web utama.</p>";
        }
    } catch (Exception $e) {
        echo "<p class='err'>⚠️ Error saat verifikasi: " . $e->getMessage() . "</p>";
        echo "<p>Tapi file sudah terekstrak. Coba refresh web utama Anda.</p>";
    }
    
    // Self-delete installer
    echo "<p class='ok'>🔒 Menghapus installer script demi keamanan...</p>";
    unlink(__FILE__);
    
    echo "<h2>🎉 SELESAI!</h2>";
    echo "<p>Silakan kembali ke <a href='/' style='color:#38bdf8;'>halaman utama</a>.</p>";
    
} else {
    echo "<p class='err'>❌ Gagal membuka zip! Error code: $res</p>";
}

echo "</body></html>";
