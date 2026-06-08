<?php
/**
 * Diagnostic & Auto-Fix Script
 * Akses: https://absensirecord.fwh.is/fix_vendor.php
 */
set_time_limit(120);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Fix Vendor</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;} .err{color:#f87171;} .warn{color:#fbbf24;}
h2{color:#38bdf8;} pre{background:#1e293b;padding:15px;border-radius:10px;overflow-x:auto;font-size:12px;}
</style></head><body>";

echo "<h2>🔍 Vendor Diagnostic & Fix</h2>";

$baseDir = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';

// 1. Check basic structure
echo "<h3>1. Cek Struktur Folder</h3>";

$checks = [
    'vendor/' => is_dir($vendorDir),
    'vendor/autoload.php' => file_exists($vendorDir . '/autoload.php'),
    'vendor/composer/' => is_dir($vendorDir . '/composer'),
    'vendor/shuchkin/' => is_dir($vendorDir . '/shuchkin'),
    'vendor/vendor/ (SALAH - nested)' => is_dir($vendorDir . '/vendor'),
];

foreach ($checks as $path => $exists) {
    $icon = $exists ? '✅' : '❌';
    $class = $exists ? 'ok' : 'err';
    if (strpos($path, 'SALAH') !== false) {
        $icon = $exists ? '⚠️' : '✅';
        $class = $exists ? 'warn' : 'ok';
    }
    echo "<p class='$class'>$icon $path</p>";
}

// 2. Auto-fix: if vendor/vendor exists, move contents up
if (is_dir($vendorDir . '/vendor')) {
    echo "<h3>2. Auto-Fix: Memindahkan dari vendor/vendor/ ke vendor/</h3>";
    
    function copyDir($src, $dst) {
        $dir = opendir($src);
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        $count = 0;
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') continue;
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                $count += copyDir($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
                $count++;
            }
        }
        closedir($dir);
        return $count;
    }
    
    function removeDir($dir) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                removeDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    $moved = copyDir($vendorDir . '/vendor', $vendorDir);
    echo "<p class='ok'>✅ Dipindahkan $moved file dari vendor/vendor/ ke vendor/</p>";
    
    // Remove the nested vendor/vendor folder
    removeDir($vendorDir . '/vendor');
    echo "<p class='ok'>🗑️ Folder vendor/vendor/ dihapus.</p>";
}

// 3. Re-check after fix
echo "<h3>3. Verifikasi Setelah Fix</h3>";

$finalChecks = [
    'vendor/autoload.php' => file_exists($vendorDir . '/autoload.php'),
    'vendor/composer/autoload_real.php' => file_exists($vendorDir . '/composer/autoload_real.php'),
    'vendor/shuchkin/simplexlsx/src/SimpleXLSX.php' => file_exists($vendorDir . '/shuchkin/simplexlsx/src/SimpleXLSX.php'),
];

$allGood = true;
foreach ($finalChecks as $path => $exists) {
    $icon = $exists ? '✅' : '❌';
    $class = $exists ? 'ok' : 'err';
    if (!$exists) $allGood = false;
    echo "<p class='$class'>$icon $path</p>";
}

// 4. Test autoload
echo "<h3>4. Test Autoload</h3>";
try {
    require_once $vendorDir . '/autoload.php';
    if (class_exists('Shuchkin\\SimpleXLSX')) {
        echo "<p class='ok'>✅ Shuchkin\\SimpleXLSX BERHASIL dimuat!</p>";
    } else {
        echo "<p class='err'>❌ Class Shuchkin\\SimpleXLSX tidak ditemukan.</p>";
    }
} catch (Throwable $e) {
    echo "<p class='err'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 5. Show vendor directory listing
echo "<h3>5. Isi Folder vendor/ (level 1)</h3><pre>";
if (is_dir($vendorDir)) {
    $items = scandir($vendorDir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $type = is_dir($vendorDir . '/' . $item) ? '[DIR]' : '[FILE]';
        echo "$type $item\n";
    }
}
echo "</pre>";

if ($allGood) {
    echo "<h2 class='ok'>🎉 SEMUA OK! Silakan akses web utama Anda.</h2>";
    // Self-delete
    unlink(__FILE__);
} else {
    echo "<h2 class='err'>⚠️ Masih ada masalah. Screenshot halaman ini dan kirim ke developer.</h2>";
}

echo "</body></html>";
