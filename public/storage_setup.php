<?php
/**
 * Storage Setup Script - Jalankan SEKALI lalu hapus!
 * URL: https://yourdomain.com/storage_setup.php?key=setup2024
 */

$key = $_GET['key'] ?? '';
if ($key !== 'setup2024') {
    die('Access denied. Tambahkan ?key=setup2024 ke URL.');
}

header('Content-Type: text/html; charset=utf-8');

$publicPath  = __DIR__;
$storagePath = $publicPath . '/storage';
$avatarsPath = $storagePath . '/avatars';

$results = [];

// Buat folder storage
if (!is_dir($storagePath)) {
    if (@mkdir($storagePath, 0775, true)) {
        $results[] = ['ok' => true,  'msg' => "✅ Folder <b>public/storage/</b> berhasil dibuat."];
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        $results[] = ['ok' => false, 'msg' => "❌ Gagal buat public/storage/: $err"];
    }
} else {
    $results[] = ['ok' => true, 'msg' => "✅ Folder <b>public/storage/</b> sudah ada."];
}

// Buat folder avatars
if (!is_dir($avatarsPath)) {
    if (@mkdir($avatarsPath, 0775, true)) {
        $results[] = ['ok' => true,  'msg' => "✅ Folder <b>public/storage/avatars/</b> berhasil dibuat."];
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        $results[] = ['ok' => false, 'msg' => "❌ Gagal buat public/storage/avatars/: $err"];
    }
} else {
    $results[] = ['ok' => true, 'msg' => "✅ Folder <b>public/storage/avatars/</b> sudah ada."];
}

// Set permission
if (is_dir($storagePath))  { @chmod($storagePath,  0775); }
if (is_dir($avatarsPath))  { @chmod($avatarsPath,  0775); }

// Buat .htaccess agar gambar bisa diakses
$htaccess = $storagePath . '/.htaccess';
if (!file_exists($htaccess)) {
    $content = "Options -Indexes\n<FilesMatch \"\.(jpg|jpeg|png|gif|webp)$\">\n    Allow from all\n</FilesMatch>\n";
    if (@file_put_contents($htaccess, $content)) {
        $results[] = ['ok' => true,  'msg' => "✅ File <b>.htaccess</b> dibuat di public/storage/."];
    }
}

// Test tulis file
$testFile = $avatarsPath . '/write_test_' . time() . '.txt';
$canWrite = false;
if (@file_put_contents($testFile, 'ok') !== false) {
    @unlink($testFile);
    $canWrite = true;
    $results[] = ['ok' => true,  'msg' => "✅ Test tulis ke public/storage/avatars/ <b>BERHASIL</b>!"];
} else {
    $err = error_get_last()['message'] ?? 'unknown';
    $results[] = ['ok' => false, 'msg' => "❌ Test tulis GAGAL: $err — Perlu set permission 775 manual via File Manager."];
}

// --- Buat symlink jika bisa
$symTarget = dirname(__DIR__) . '/storage/app/public';
if (!is_link($storagePath) && function_exists('symlink')) {
    // Tidak overwrite folder yang sudah ada, lewati
}

$allOk = $canWrite && is_dir($avatarsPath);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Storage Setup</title>
<style>
body { font-family: -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; padding: 32px; margin: 0; }
h2  { color: #60a5fa; }
.card { background: #1e293b; border-radius: 12px; padding: 20px 24px; margin-bottom: 16px; }
.ok  { color: #22c55e; }
.err { color: #ef4444; }
.item { padding: 10px 0; border-bottom: 1px solid #334155; font-size: 14px; }
.item:last-child { border: none; }
.banner { border-radius: 12px; padding: 20px 24px; font-size: 16px; font-weight: bold; margin-bottom: 24px; }
.banner-ok  { background: #14532d; color: #86efac; border: 1px solid #166534; }
.banner-err { background: #450a0a; color: #fca5a5; border: 1px solid #7f1d1d; }
code { background: #0f172a; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
</style>
</head>
<body>
<h2>⚙️ Storage Setup — Dewa Web Hosting</h2>

<div class="banner <?= $allOk ? 'banner-ok' : 'banner-err' ?>">
    <?= $allOk
        ? '🎉 Setup BERHASIL! Folder storage sudah siap. Sekarang upload foto profil harus bisa.'
        : '⚠️ Setup sebagian gagal. Baca detail di bawah dan buat folder manual via File Manager.' ?>
</div>

<div class="card">
    <?php foreach ($results as $r): ?>
        <div class="item <?= $r['ok'] ? 'ok' : 'err' ?>"><?= $r['msg'] ?></div>
    <?php endforeach; ?>
</div>

<?php if (!$allOk): ?>
<div class="card">
    <h3 style="color:#f59e0b;margin-top:0;">📁 Cara Manual via File Manager cPanel</h3>
    <ol style="line-height:2;font-size:14px;">
        <li>Login ke cPanel Dewa Web → buka <b>File Manager</b></li>
        <li>Navigasi ke: <code><?= $publicPath ?></code></li>
        <li>Klik <b>New Folder</b> → beri nama <code>storage</code></li>
        <li>Masuk ke folder <code>storage</code> yang baru dibuat</li>
        <li>Klik <b>New Folder</b> lagi → beri nama <code>avatars</code></li>
        <li>Klik kanan folder <code>storage</code> → <b>Change Permission</b> → set ke <code>755</code></li>
        <li>Lakukan hal yang sama untuk folder <code>avatars</code></li>
        <li>Reload halaman ini untuk verifikasi</li>
    </ol>
</div>
<?php endif; ?>

<?php if ($allOk): ?>
<div class="card" style="border: 1px solid #166534;">
    <h3 style="color:#22c55e;margin-top:0;">✅ Langkah Selanjutnya</h3>
    <ol style="line-height:2;font-size:14px;">
        <li>Coba ganti foto profil di aplikasi Anda sekarang</li>
        <li><b>Hapus file ini</b> dari hosting: <code><?= __FILE__ ?></code></li>
        <li>Hapus juga <code>avatar_diag.php</code> jika sudah tidak diperlukan</li>
    </ol>
</div>
<?php endif; ?>

<p style="color:#475569;font-size:11px;margin-top:32px;">
    ⚠️ <b>Hapus file ini setelah selesai!</b>: <code><?= __FILE__ ?></code>
</p>
</body>
</html>
