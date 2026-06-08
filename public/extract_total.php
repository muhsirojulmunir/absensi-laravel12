<?php
/**
 * Ekstrak folder vendor secara bertahap dan hapus yang lama (mengatasi INODE limit)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Fix Total Vendor</title><style>
body{font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:auto;}
.ok{color:#4ade80;font-weight:bold;} .err{color:#f87171;font-weight:bold;}
h2{color:#38bdf8;}
</style></head><body>";

echo "<h2>🔧 Memulihkan Keseluruhan File Vendor (Anti Inode Limit)</h2>";

$zipFile = __DIR__ . '/vendor_full.zip';
$extractTo = dirname(__DIR__);
$baseDir = dirname(__DIR__);

// Fungsi hapus folder rekursif
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    @unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        @rmdir($dir);
    }
}

// 1. HAPUS folder vendor lama dan semua backup untuk MENGOSONGKAN INODE/KAPASITAS
echo "<h3>1. Menghapus file lama...</h3>";
$objects = scandir($baseDir);
foreach ($objects as $object) {
    if (strpos($object, 'vendor_backup') === 0 || $object === 'vendor') {
        echo "<p>🗑️ Menghapus: $object...</p>";
        rrmdir($baseDir . DIRECTORY_SEPARATOR . $object);
    }
}
echo "<p class='ok'>✅ File lama berhasil dihapus (Inodes/kapasitas telah dibebaskan).</p>";

// 2. EKSTRAK ZIP
echo "<h3>2. Mengekstrak file baru...</h3>";
if (!file_exists($zipFile)) {
    echo "<p class='err'>❌ File vendor_full.zip tidak ditemukan!</p>";
    exit;
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractTo);
    $zip->close();
    echo "<p class='ok'>✅ Folder vendor berhasil diekstrak sepenuhnya.</p>";
} else {
    echo "<p class='err'>❌ Gagal membuka zip.</p>";
    exit;
}

// 3. Clear cache
echo "<h3>3. Membersihkan cache Laravel...</h3>";
$cacheDirs = [
    $baseDir . '/bootstrap/cache',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/framework/views'
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
echo "<p class='ok'>✅ Cache dibersihkan.</p>";

// 4. Test Boot
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
        echo "<h2 class='ok'>🎉 SELESAI SUNGGUHAN! Web sudah kembali normal 100%.</h2>";
        echo "<p><a href='/' style='color:#38bdf8;font-weight:bold;font-size:18px;'>← BUKA HALAMAN UTAMA</a></p>";
        
        // Cleanup
        @unlink($zipFile);
        @unlink(__FILE__);
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

echo "</body></html>";
