<?php
/**
 * Auto Updater - Mengambil file terbaru dari GitHub
 * ⚠️ HAPUS SETELAH SELESAI!
 * URL: https://yourdomain.com/auto_update.php?key=update2024
 */

$key = $_GET['key'] ?? '';
if ($key !== 'update2024') {
    die('Access denied.');
}

header('Content-Type: text/html; charset=utf-8');

// ====== KONFIGURASI ======
// Root Laravel (parent dari public/)
$root = dirname(__DIR__);

// Daftar file yang akan diupdate dari GitHub raw content
// Format: [path relatif dari root Laravel => URL raw GitHub]
$githubUser = 'muhsirojulmunir';
$githubRepo = 'absensi-laravel12';
$branch     = 'main';

$baseUrl = "https://raw.githubusercontent.com/{$githubUser}/{$githubRepo}/{$branch}";

$filesToUpdate = [
    'app/Http/Controllers/Karyawan/LeaveRequestController.php',
    'resources/views/karyawan/leave-requests/index.blade.php',
    'app/Http/Controllers/Karyawan/ProfileController.php',
    'resources/views/karyawan/profile/edit.blade.php',
    'app/Http/Controllers/SuperAdmin/RamayanaStockController.php',
    'app/Http/Controllers/PIC/RamayanaStockController.php',
    'app/Services/ExcelImportReader.php',
    'routes/web.php',
    'config/filesystems.php',
    'public/storage_setup.php',
];
// ====== END KONFIGURASI ======

$results = [];

// Buat folder storage jika belum ada
$storageFolders = [
    $root . '/public/storage',
    $root . '/public/storage/avatars',
    $root . '/public/storage/lupa-absen',
];
foreach ($storageFolders as $sf) {
    if (!is_dir($sf)) {
        @mkdir($sf, 0775, true);
    }
    if (is_dir($sf)) {
        @chmod($sf, 0775);
    }
}

foreach ($filesToUpdate as $relativePath) {
    $targetPath = $root . '/' . $relativePath;
    $sourceUrl  = $baseUrl . '/' . $relativePath;

    // Buat direktori jika belum ada
    $dir = dirname($targetPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    // Download konten file dari GitHub
    $context = stream_context_create([
        'http' => [
            'timeout'        => 15,
            'ignore_errors'  => true,
            'user_agent'     => 'PHP AutoUpdater/1.0',
        ]
    ]);

    $content = @file_get_contents($sourceUrl, false, $context);

    if ($content === false || empty($content)) {
        $results[] = [
            'path'    => $relativePath,
            'ok'      => false,
            'msg'     => 'Gagal download dari GitHub. URL: ' . $sourceUrl,
        ];
        continue;
    }

    // Tulis ke file target
    $written = @file_put_contents($targetPath, $content);

    if ($written !== false) {
        $results[] = [
            'path'    => $relativePath,
            'ok'      => true,
            'msg'     => 'Berhasil diupdate (' . number_format($written) . ' bytes)',
        ];
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        $results[] = [
            'path'    => $relativePath,
            'ok'      => false,
            'msg'     => 'Download OK tapi gagal menulis ke file: ' . $err,
        ];
    }
}

// Hapus cache config/view jika ada
$cacheFiles = [
    $root . '/bootstrap/cache/config.php',
    $root . '/bootstrap/cache/routes-v7.php',
];
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        @unlink($cf);
    }
}

// Hapus cached views
$viewsCache = $root . '/storage/framework/views';
if (is_dir($viewsCache)) {
    foreach (glob($viewsCache . '/*.php') as $f) {
        @unlink($f);
    }
}

$allOk = !in_array(false, array_column($results, 'ok'));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Auto Updater</title>
<style>
body { font-family: -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; padding: 32px; margin: 0; }
h2  { color: #60a5fa; }
.banner { border-radius: 12px; padding: 20px 24px; font-size: 15px; font-weight: bold; margin-bottom: 24px; }
.banner-ok  { background: #14532d; color: #86efac; border: 1px solid #166534; }
.banner-err { background: #450a0a; color: #fca5a5; border: 1px solid #7f1d1d; }
.card { background: #1e293b; border-radius: 12px; padding: 4px 0; margin-bottom: 16px; }
.item { padding: 14px 20px; border-bottom: 1px solid #334155; font-size: 13px; display: flex; gap: 16px; align-items: flex-start; }
.item:last-child { border: none; }
.ok  { color: #22c55e; font-weight: bold; min-width: 24px; }
.err { color: #ef4444; font-weight: bold; min-width: 24px; }
code { background: #0f172a; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 11px; }
</style>
</head>
<body>
<h2>🔄 Auto Updater — Pull from GitHub</h2>

<div class="banner <?= $allOk ? 'banner-ok' : 'banner-err' ?>">
    <?= $allOk
        ? '🎉 Semua file berhasil diupdate dari GitHub! Foto profil seharusnya sudah bisa diubah.'
        : '⚠️ Sebagian file gagal diupdate. Cek detail di bawah.' ?>
</div>

<div class="card">
    <?php foreach ($results as $r): ?>
    <div class="item">
        <span class="<?= $r['ok'] ? 'ok' : 'err' ?>"><?= $r['ok'] ? '✅' : '❌' ?></span>
        <div>
            <code><?= htmlspecialchars($r['path']) ?></code><br>
            <span style="color:<?= $r['ok'] ? '#86efac' : '#fca5a5' ?>;font-size:12px;"><?= htmlspecialchars($r['msg']) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="item">
        <span class="ok">🗑️</span>
        <div>
            <code>bootstrap/cache & storage/framework/views</code><br>
            <span style="color:#86efac;font-size:12px;">Cache config dan view telah dibersihkan.</span>
        </div>
    </div>
</div>

<?php if ($allOk): ?>
<div style="background:#1e293b;border-radius:12px;padding:20px 24px;border:1px solid #166534;">
    <h3 style="color:#22c55e;margin-top:0;">✅ Langkah Selanjutnya</h3>
    <ol style="line-height:2;font-size:14px;">
        <li>Buka aplikasi dan coba ganti foto profil sekarang</li>
        <li>Jika sudah berhasil, <b>hapus file-file ini</b> dari hosting:<br>
            <code>public/auto_update.php</code><br>
            <code>public/avatar_diag.php</code><br>
            <code>public/storage_setup.php</code>
        </li>
    </ol>
</div>
<?php endif; ?>

<p style="color:#475569;font-size:11px;margin-top:32px;">⚠️ Hapus file ini: <code><?= __FILE__ ?></code></p>
</body>
</html>
