<?php
/**
 * Force clear cache AND update bootstrap/cache
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Force Cache Reset</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body><h2>🔧 Force Cache Reset</h2>";

$baseDir = dirname(__DIR__);

// 1. HAPUS FOLDER CACHE SECARA REKURSIF
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != ".." && $object != ".gitignore") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    @unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
    }
}

rrmdir($baseDir . '/bootstrap/cache');
echo "<p class='ok'>✅ bootstrap/cache dibersihkan</p>";

rrmdir($baseDir . '/storage/framework/cache');
echo "<p class='ok'>✅ storage/framework/cache dibersihkan</p>";

rrmdir($baseDir . '/storage/framework/views');
echo "<p class='ok'>✅ storage/framework/views dibersihkan</p>";

// 2. EKSTRAK CACHE BARU (yg sudah bersih)
$zipFile = __DIR__ . '/bootstrap_cache.zip';
if (file_exists($zipFile)) {
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($baseDir . '/bootstrap/cache');
        $zip->close();
        echo "<p class='ok'>✅ bootstrap/cache diperbarui dari zip lokal</p>";
    }
}

// 3. TEST BOOT
echo "<h3>🧪 Test Boot Laravel</h3>";
try {
    require_once $baseDir . '/vendor/autoload.php';
    $app = require_once $baseDir . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    if ($response->getStatusCode() !== 500) {
        echo "<h2 class='ok'>🎉 SELESAI! Web sudah kembali normal.</h2>";
        @unlink($zipFile);
        @unlink(__FILE__);
    } else {
        echo "<p class='err'>❌ Masih Error 500.</p>";
    }
} catch (Throwable $e) {
    echo "<p class='err'>❌ " . get_class($e) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
