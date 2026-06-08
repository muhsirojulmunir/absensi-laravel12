<?php
/**
 * Fix session/cookie issue pada InfinityFree
 * Ubah SESSION_DRIVER dari database ke file
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Fix Session</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🔧 Fix Session/Cookie</h2>";

$envFile = dirname(__DIR__) . '/.env';

if (!file_exists($envFile)) {
    echo "<p class='err'>❌ File .env tidak ditemukan!</p>";
    exit;
}

$content = file_get_contents($envFile);

// 1. Ubah SESSION_DRIVER dari database ke file
$content = preg_replace('/SESSION_DRIVER=database/', 'SESSION_DRIVER=file', $content);
echo "<p class='ok'>✅ SESSION_DRIVER diubah ke 'file'</p>";

// 2. Set SESSION_SECURE_COOKIE ke false (InfinityFree kadang pakai HTTP)
if (strpos($content, 'SESSION_SECURE_COOKIE') === false) {
    $content .= "\nSESSION_SECURE_COOKIE=false\n";
    echo "<p class='ok'>✅ SESSION_SECURE_COOKIE=false ditambahkan</p>";
}

// 3. Set SESSION_SAME_SITE ke lax
if (strpos($content, 'SESSION_SAME_SITE') === false) {
    $content .= "SESSION_SAME_SITE=lax\n";
    echo "<p class='ok'>✅ SESSION_SAME_SITE=lax ditambahkan</p>";
}

// 4. Ubah CACHE_STORE dari database ke file
$content = preg_replace('/CACHE_STORE=database/', 'CACHE_STORE=file', $content);
echo "<p class='ok'>✅ CACHE_STORE diubah ke 'file'</p>";

// 5. Ubah QUEUE_CONNECTION dari database ke sync
$content = preg_replace('/QUEUE_CONNECTION=database/', 'QUEUE_CONNECTION=sync', $content);
echo "<p class='ok'>✅ QUEUE_CONNECTION diubah ke 'sync'</p>";

// 5b. Update APP_URL dan LIVE_URL untuk domain baru
$content = preg_replace('/APP_URL=.*/', 'APP_URL=http://jmnmatrix.rf.gd', $content);
$content = preg_replace('/LIVE_URL=.*/', 'LIVE_URL=jmnmatrix.rf.gd', $content);
echo "<p class='ok'>✅ URL diubah ke jmnmatrix.rf.gd</p>";

file_put_contents($envFile, $content);

// 6. Pastikan folder sessions ada
$sessionsDir = dirname(__DIR__) . '/storage/framework/sessions';
if (!is_dir($sessionsDir)) {
    mkdir($sessionsDir, 0755, true);
    echo "<p class='ok'>✅ Folder storage/framework/sessions dibuat</p>";
} else {
    echo "<p class='ok'>✅ Folder sessions sudah ada</p>";
}

// 7. Clear config cache
$cacheDirs = [
    dirname(__DIR__) . '/bootstrap/cache',
];
foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..', '.gitignore']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_file($path)) @unlink($path);
        }
    }
}
echo "<p class='ok'>✅ Bootstrap cache dibersihkan</p>";

// Test
echo "<h3>🧪 Test</h3>";
try {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    if ($response->getStatusCode() !== 500) {
        echo "<h2 class='ok'>🎉 BERHASIL! Web sudah normal.</h2>";
        echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:18px;'>← BUKA HALAMAN UTAMA</a></p>";
        @unlink(__FILE__);
    } else {
        echo "<p class='err'>❌ Status " . $response->getStatusCode() . "</p>";
    }
} catch (Throwable $e) {
    echo "<p class='err'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
