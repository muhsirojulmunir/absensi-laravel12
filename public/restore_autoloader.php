<?php
/**
 * Restore vendor/composer autoloader
 * Akses: https://absensirecord.fwh.is/restore_autoloader.php
 */
set_time_limit(60);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Restore Autoloader</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🔧 Restore Composer Autoloader</h2>";

$vendorDir = dirname(__DIR__) . '/vendor';
$composerDir = $vendorDir . '/composer';

// Cek apakah installed.php ada (ini file yang menandakan vendor pernah diinstall lengkap)
$installedFile = $composerDir . '/installed.php';
if (!file_exists($installedFile)) {
    $installedFile = $composerDir . '/installed.json';
}

// Backup: baca autoload_psr4.php yang ada
$psr4File = $composerDir . '/autoload_psr4.php';
$hasPsr4 = file_exists($psr4File);

echo "<p>Composer dir: " . (is_dir($composerDir) ? '✅ Ada' : '❌ Tidak ada') . "</p>";
echo "<p>PSR4 file: " . ($hasPsr4 ? '✅ Ada' : '❌ Tidak ada') . "</p>";

// Kita perlu membuat autoload_real.php yang TIDAK mereferensikan file yang hilang
// Baca file autoload_real.php yang bermasalah
$autoloadRealFile = $composerDir . '/autoload_real.php';

if (file_exists($autoloadRealFile)) {
    $content = file_get_contents($autoloadRealFile);
    
    // Hapus semua baris require yang mereferensikan paket di luar composer/ 
    // Pattern: require yang mengarah ke ../some-vendor/
    $content = preg_replace(
        "/\\\$vendorDir\s*\.\s*'\/[^']*'\s*,?\s*\n?/", 
        "",
        $content
    );
    
    // Lebih agresif: cari dan hapus blok $includeFiles
    // Ganti seluruh bagian autoload_files yang mereferensikan vendor packages
    $content = preg_replace(
        '/\$includeFiles\s*=.*?;/s',
        '$includeFiles = array();',
        $content
    );
    
    file_put_contents($autoloadRealFile, $content);
    echo "<p class='ok'>✅ autoload_real.php diperbaiki (hapus referensi file hilang)</p>";
}

// Fix autoload_static.php juga
$autoloadStaticFile = $composerDir . '/autoload_static.php';
if (file_exists($autoloadStaticFile)) {
    $content = file_get_contents($autoloadStaticFile);
    
    // Hapus entries di $files array yang mereferensikan paket yang tidak ada
    // Kita hanya perlu menghapus entries yang file-nya tidak exist
    if (preg_match_all("/'([a-f0-9]+)'\s*=>\s*\\\$vendorDir\s*\.\s*'([^']+)'/", $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $filePath = $vendorDir . $match[2];
            if (!file_exists($filePath)) {
                // Hapus entry ini
                $content = str_replace($match[0] . ",\n", '', $content);
                $content = str_replace($match[0] . ",", '', $content);
                $content = str_replace($match[0], '', $content);
                echo "<p>🗑️ Hapus referensi file tidak ada: " . $match[2] . "</p>";
            }
        }
    }
    
    file_put_contents($autoloadStaticFile, $content);
    echo "<p class='ok'>✅ autoload_static.php diperbaiki</p>";
}

// Fix autoload_files.php
$autoloadFilesFile = $composerDir . '/autoload_files.php';
if (file_exists($autoloadFilesFile)) {
    $content = file_get_contents($autoloadFilesFile);
    
    if (preg_match_all("/'([a-f0-9]+)'\s*=>\s*\\\$vendorDir\s*\.\s*'([^']+)'/", $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $filePath = $vendorDir . $match[2];
            if (!file_exists($filePath)) {
                $content = str_replace($match[0] . ",\n", '', $content);
                $content = str_replace($match[0] . ",", '', $content);
                $content = str_replace($match[0], '', $content);
                echo "<p>🗑️ Hapus dari autoload_files: " . $match[2] . "</p>";
            }
        }
    }
    
    file_put_contents($autoloadFilesFile, $content);
    echo "<p class='ok'>✅ autoload_files.php diperbaiki</p>";
}

// Test
echo "<h2>🧪 Test Autoload</h2>";
try {
    require_once $vendorDir . '/autoload.php';
    echo "<p class='ok'>✅ Autoloader berhasil dimuat tanpa error!</p>";
    
    echo "<h2 class='ok'>🎉 SELESAI! Web Anda seharusnya sudah normal.</h2>";
    echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;'>← Kembali ke halaman utama</a></p>";
    
    // Self-delete
    unlink(__FILE__);
} catch (Throwable $e) {
    echo "<p class='err'>❌ Masih error: " . $e->getMessage() . "</p>";
    echo "<p>Coba refresh halaman ini sekali lagi.</p>";
}

echo "</body></html>";
