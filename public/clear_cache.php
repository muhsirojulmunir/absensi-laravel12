<?php
/**
 * Clear Laravel Caches
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Clear Cache</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🧹 Membersihkan Cache Laravel</h2>";

$baseDir = dirname(__DIR__);
$cacheDirs = [
    $baseDir . '/bootstrap/cache',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/framework/views'
];

$clearedFiles = 0;

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..', '.gitignore']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_file($path)) {
                @unlink($path);
                $clearedFiles++;
            }
        }
        echo "<p class='ok'>✅ Dibersihkan: " . str_replace($baseDir, '', $dir) . "</p>";
    }
}

echo "<p>Total file cache dihapus: $clearedFiles</p>";

// Cek apakah ada file config/app.php
$appConfig = $baseDir . '/config/app.php';
if (!file_exists($appConfig)) {
    echo "<p class='err'>❌ Peringatan: config/app.php tidak ada!</p>";
}

echo "<h3>🧪 Test Boot Laravel</h3>";
try {
    require_once $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "<p class='ok'>✅ LARAVEL BOOTS SUCCESSFULLY! (Status: " . $response->getStatusCode() . ")</p>";
    
    if ($response->getStatusCode() !== 500) {
        echo "<h2 class='ok'>🎉 WEB ANDA SUDAH NORMAL!</h2>";
        echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:18px;'>← BUKA HALAMAN UTAMA</a></p>";
    } else {
        echo "<p class='err'>❌ Masih Error 500. Menampilkan isi error:</p>";
        $content = $response->getContent();
        if (strlen($content) > 0) {
            echo "<div style='background:#fff;color:#000;padding:10px;border-radius:5px;max-height:400px;overflow:auto;'>" . $content . "</div>";
        }
    }
    
    @unlink(__FILE__);
    @unlink(dirname(__FILE__) . '/extract_composer.php');
} catch (Throwable $e) {
    echo "<p class='err'>❌ " . get_class($e) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
