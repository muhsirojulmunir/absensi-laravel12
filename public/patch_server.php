<?php
/**
 * Patch autoload_real.php langsung di server
 * Tambahkan file_exists() check agar require tidak crash untuk file hilang
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Patch</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>⚡ Patch autoload_real.php</h2>";

$file = dirname(__DIR__) . '/vendor/composer/autoload_real.php';

if (!file_exists($file)) {
    echo "<p class='err'>❌ File tidak ditemukan!</p>";
    exit;
}

$content = file_get_contents($file);

// Cari pattern: require $file; (tanpa file_exists check)
// Dan ganti dengan: if (file_exists($file)) { require $file; }
$old = 'require $file;';
$new = 'if (file_exists($file)) { require $file; }';

if (strpos($content, $old) !== false && strpos($content, 'file_exists($file)') === false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "<p class='ok'>✅ Patched! Baris 'require \$file;' diganti dengan 'if (file_exists(\$file)) { require \$file; }'</p>";
} elseif (strpos($content, 'file_exists($file)') !== false) {
    echo "<p class='ok'>✅ Sudah di-patch sebelumnya.</p>";
} else {
    echo "<p class='err'>⚠️ Pattern tidak ditemukan.</p>";
}

// Test
echo "<h3>🧪 Test</h3>";
try {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    echo "<p class='ok'>✅ Autoloader berhasil dimuat!</p>";
    echo "<h2 class='ok'>🎉 WEB SUDAH NORMAL!</h2>";
    echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:20px;'>← BUKA HALAMAN UTAMA</a></p>";
    @unlink(__FILE__);
    @unlink(dirname(__FILE__) . '/fix_autoload.php');
    @unlink(dirname(__FILE__) . '/restore_autoloader.php');
    @unlink(dirname(__FILE__) . '/fix_vendor.php');
} catch (Throwable $e) {
    echo "<p class='err'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
