<?php
/**
 * Ekstrak folder composer yang bersih
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Fix Autoloader</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🔧 Memulihkan Autoloader (Final)</h2>";

$zipFile = __DIR__ . '/vendor_composer_fix.zip';
$extractTo = dirname(__DIR__) . '/vendor'; // Ekstrak ke folder vendor (di dalamnya ada folder composer)

if (!file_exists($zipFile)) {
    echo "<p class='err'>❌ File vendor_composer_fix.zip tidak ditemukan!</p>";
    exit;
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    // Ekstrak ke folder vendor
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<p class='ok'>✅ Folder composer berhasil dipulihkan dari backup bersih.</p>";
    
    // Test
    try {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        echo "<p class='ok'>✅ Autoloader berhasil dimuat!</p>";
        echo "<h2 class='ok'>🎉 SELESAI! Web sudah kembali normal.</h2>";
        echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:18px;'>← BUKA HALAMAN UTAMA</a></p>";
        
        // Hapus file zip & script
        @unlink($zipFile);
        @unlink(__FILE__);
        @unlink(dirname(__FILE__) . '/patch_server.php');
        @unlink(dirname(__FILE__) . '/diagnose.php');
        
    } catch (Throwable $e) {
        echo "<p class='err'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='err'>❌ Gagal mengekstrak zip.</p>";
}

echo "</body></html>";
