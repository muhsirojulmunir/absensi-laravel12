<?php
/**
 * Ekstrak folder laravel/framework yang bersih
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Fix Framework</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🔧 Memulihkan File Inti Laravel (Final Part 2)</h2>";

$zipFile = __DIR__ . '/vendor_laravel_framework.zip';
$extractTo = dirname(__DIR__) . '/vendor/laravel'; // Ekstrak ke folder vendor/laravel (akan menimpa folder framework)

if (!file_exists($zipFile)) {
    echo "<p class='err'>❌ File vendor_laravel_framework.zip tidak ditemukan!</p>";
    exit;
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    // Ekstrak ke folder vendor/laravel
    $zip->extractTo($extractTo);
    $zip->close();
    
    echo "<p class='ok'>✅ Folder laravel/framework berhasil dipulihkan dari backup bersih.</p>";
    
    // Clear cache lagi untuk berjaga-jaga
    $baseDir = dirname(__DIR__);
    $cacheDirs = [
        $baseDir . '/bootstrap/cache',
        $baseDir . '/storage/framework/cache'
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
    
    // Test
    echo "<h3>🧪 Test Boot Laravel</h3>";
    try {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(
            $request = Illuminate\Http\Request::capture()
        );
        echo "<p class='ok'>✅ LARAVEL BOOTS SUCCESSFULLY! (Status: " . $response->getStatusCode() . ")</p>";
        
        if ($response->getStatusCode() !== 500) {
            echo "<h2 class='ok'>🎉 SELESAI! Web sudah kembali normal.</h2>";
            echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:18px;'>← BUKA HALAMAN UTAMA</a></p>";
            
            // Cleanup
            @unlink($zipFile);
            @unlink(__FILE__);
            @unlink(dirname(__FILE__) . '/clear_cache.php');
        } else {
            echo "<p class='err'>❌ Masih Error 500. Menampilkan isi error:</p>";
            $content = $response->getContent();
            if (strlen($content) > 0) {
                echo "<div style='background:#fff;color:#000;padding:10px;border-radius:5px;max-height:400px;overflow:auto;'>" . $content . "</div>";
            }
        }
        
    } catch (Throwable $e) {
        echo "<p class='err'>❌ " . get_class($e) . ": " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='err'>❌ Gagal mengekstrak zip.</p>";
}

echo "</body></html>";
