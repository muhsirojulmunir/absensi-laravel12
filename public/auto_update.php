<?php
/**
 * Auto Updater - Mengambil file terbaru dari GitHub
 * ⚠️ HAPUS SETELAH SELESAI!
 * URL: https://yourdomain.com/auto_update.php?key=update2024
 */

$key = $_GET['key'] ?? '';
if ($key !== 'update2024') {
    die('Access denied. Gunakan ?key=update2024');
}

header('Content-Type: text/html; charset=utf-8');

// ====== KONFIGURASI ======
$root = dirname(__DIR__);

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

/**
 * Fungsi download file dengan cURL + fallback file_get_contents
 */
function downloadFileContent($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/plain, */*',
                'Cache-Control: no-cache',
            ],
        ]);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg = curl_error($ch);
        curl_close($ch);

        if ($data !== false && $httpCode >= 200 && $httpCode < 300 && strlen($data) > 0) {
            return ['ok' => true, 'data' => $data];
        }

        $detail = $errorMsg ? "cURL: $errorMsg (HTTP $httpCode)" : "HTTP $httpCode";
    } else {
        $detail = "cURL tidak aktif di server";
    }

    // Fallback ke file_get_contents
    $ctx = stream_context_create([
        'http' => [
            'timeout'        => 25,
            'ignore_errors'  => true,
            'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ]);

    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 0) {
        return ['ok' => true, 'data' => $data];
    }

    $lastErr = error_get_last()['message'] ?? 'Unknown error';
    return ['ok' => false, 'msg' => "$detail | fopen: $lastErr"];
}

$results = [];

// 1. Buat folder storage jika belum ada
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

// 2. Download dan update file dari GitHub
foreach ($filesToUpdate as $relativePath) {
    $targetPath = $root . '/' . $relativePath;
    $sourceUrl  = $baseUrl . '/' . $relativePath . '?v=' . time(); // cache buster

    $dir = dirname($targetPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $download = downloadFileContent($sourceUrl);

    if (!$download['ok']) {
        $results[] = [
            'path' => $relativePath,
            'ok'   => false,
            'msg'  => 'Gagal download dari GitHub: ' . ($download['msg'] ?? 'Error tidak diketahui'),
        ];
        continue;
    }

    $written = @file_put_contents($targetPath, $download['data']);

    if ($written !== false) {
        $results[] = [
            'path' => $relativePath,
            'ok'   => true,
            'msg'  => 'Berhasil diupdate (' . number_format($written) . ' bytes)',
        ];
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        $results[] = [
            'path' => $relativePath,
            'ok'   => false,
            'msg'  => 'Download OK tapi gagal menulis ke file: ' . $err,
        ];
    }
}

// 3. Bersihkan cache framework
$cacheFiles = [
    $root . '/bootstrap/cache/config.php',
    $root . '/bootstrap/cache/routes-v7.php',
    $root . '/bootstrap/cache/packages.php',
    $root . '/bootstrap/cache/services.php',
];
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        @unlink($cf);
    }
}

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
        ? '🎉 Semua file berhasil diupdate dari GitHub! Pengajuan Lupa Absen dan Foto Profil sudah siap digunakan.'
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
        <span class="ok">📁</span>
        <div>
            <code>public/storage/avatars & public/storage/lupa-absen</code><br>
            <span style="color:#86efac;font-size:12px;">Folder penyimpanan foto telah disiapkan dengan permission 0775.</span>
        </div>
    </div>
    <div class="item">
        <span class="ok">🗑️</span>
        <div>
            <code>bootstrap/cache & storage/framework/views</code><br>
            <span style="color:#86efac;font-size:12px;">Cache config, route, dan view telah dibersihkan.</span>
        </div>
    </div>
</div>

<?php if ($allOk): ?>
<div style="background:#1e293b;border-radius:12px;padding:20px 24px;border:1px solid #166534;">
    <h3 style="color:#22c55e;margin-top:0;">✅ Langkah Selanjutnya</h3>
    <ol style="line-height:2;font-size:14px;">
        <li>Buka aplikasi dan coba kirim pengajuan <b>Lupa Absen</b> dengan foto bukti kamera sekarang</li>
        <li>Jika sudah selesai dan berjalan lancar, <b>hapus file-file ini</b> dari hosting:<br>
            <code>public/auto_update.php</code><br>
            <code>public/storage_setup.php</code>
        </li>
    </ol>
</div>
<?php endif; ?>

<p style="color:#475569;font-size:11px;margin-top:32px;">⚠️ Hapus file ini setelah selesai: <code><?= __FILE__ ?></code></p>
</body>
</html>
