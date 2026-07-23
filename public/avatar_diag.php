<?php
/**
 * Avatar Diagnostic Script - Diakses via browser
 * ⚠️ HAPUS FILE INI SETELAH SELESAI DEBUGGING!
 * URL: https://yourdomain.com/avatar_diag.php?key=debug2024
 */

$key = $_GET['key'] ?? '';
if ($key !== 'debug2024') {
    die('Access denied. Tambahkan ?key=debug2024 ke URL.');
}

header('Content-Type: text/html; charset=utf-8');

$publicPath  = __DIR__;
$storagePath = $publicPath . '/storage';
$avatarsPath = $storagePath . '/avatars';

// Temukan Laravel root (satu level di atas public/)
$laravelRoot = dirname(__DIR__);
if (!file_exists($laravelRoot . '/artisan')) {
    $laravelRoot = null;
}

function renderRow($path, $label) {
    $exists   = is_dir($path) || is_file($path);
    $writable = $exists ? is_writable($path) : false;
    $perms    = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    if ($exists && $writable) {
        $color  = '#22c55e';
        $status = '✅ ADA & BISA DITULIS';
    } elseif ($exists) {
        $color  = '#f59e0b';
        $status = '⚠️ ADA tapi TIDAK BISA DITULIS';
    } else {
        $color  = '#ef4444';
        $status = '❌ TIDAK ADA';
    }
    echo "<tr>
        <td style='padding:8px 12px;font-family:monospace;font-size:12px;word-break:break-all;'>$path</td>
        <td style='padding:8px 12px;font-weight:bold;color:$color;'>$status</td>
        <td style='padding:8px 12px;font-family:monospace;'>$perms</td>
    </tr>";
    return $exists && $writable;
}

function isFuncAvailable($name) {
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    return function_exists($name) && !in_array($name, $disabled);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Avatar Diagnostic</title>
<style>
* { box-sizing: border-box; }
body { font-family: -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; padding: 24px; margin: 0; }
h2  { color: #60a5fa; margin-bottom: 4px; }
h3  { color: #94a3b8; border-bottom: 1px solid #334155; padding-bottom: 8px; margin-top: 32px; }
table { border-collapse: collapse; width: 100%; background: #1e293b; border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
th { background: #334155; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: .05em; }
td { border-top: 1px solid #334155; vertical-align: top; }
.box { background: #1e293b; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; font-family: monospace; font-size: 13px; line-height: 1.8; }
.ok   { color: #22c55e; }
.warn { color: #f59e0b; }
.err  { color: #ef4444; }
a { color: #60a5fa; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.badge-ok  { background: #14532d; color: #86efac; }
.badge-err { background: #450a0a; color: #fca5a5; }
</style>
</head>
<body>
<h2>🔍 Avatar Diagnostic — Dewa Web Hosting</h2>
<p style="color:#64748b;font-size:13px;">Script ini membantu mendiagnosis kenapa upload foto profil gagal di hosting.</p>

<h3>1. Informasi Path Server</h3>
<div class="box">
    <b>Script ini berada di:</b> <?= __FILE__ ?><br>
    <b>Folder public/ (document root):</b> <?= $publicPath ?><br>
    <b>Laravel root (parent):</b> <?= $laravelRoot ?? '<span class="err">❌ TIDAK DITEMUKAN (artisan tidak ada)</span>' ?><br>
    <b>Target folder storage:</b> <?= $storagePath ?><br>
    <b>Target folder avatars:</b> <?= $avatarsPath ?><br>
    <b>PHP Version:</b> <?= PHP_VERSION ?><br>
    <b>Server OS:</b> <?= PHP_OS ?><br>
    <b>DOCUMENT_ROOT:</b> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?><br>
</div>

<h3>2. Cek Eksistensi & Permission Folder</h3>
<table>
    <tr><th>Path</th><th>Status</th><th>Chmod</th></tr>
    <?php
    renderRow($publicPath,  'public/');
    renderRow($storagePath, 'public/storage/');
    renderRow($avatarsPath, 'public/storage/avatars/');
    if ($laravelRoot) {
        renderRow($laravelRoot . '/storage',            'storage/');
        renderRow($laravelRoot . '/storage/app',        'storage/app/');
        renderRow($laravelRoot . '/storage/app/public', 'storage/app/public/');
        renderRow($laravelRoot . '/storage/app/public/avatars', 'storage/app/public/avatars/');
    }
    ?>
</table>

<h3>3. Test: Buat Folder avatars (mkdir)</h3>
<div class="box">
<?php
if (is_dir($avatarsPath)) {
    echo "<span class='ok'>✅ Folder avatars sudah ADA. Tidak perlu dibuat.</span><br>";
} else {
    $r = @mkdir($avatarsPath, 0775, true);
    if ($r) {
        echo "<span class='ok'>✅ Berhasil membuat folder dengan mkdir()</span><br>";
    } else {
        $err = error_get_last()['message'] ?? 'unknown error';
        echo "<span class='err'>❌ mkdir() GAGAL: $err</span><br>";
        echo "<span class='warn'>→ Coba buat folder 'storage/avatars' secara manual via File Manager cPanel!</span><br>";
    }
}
?>
</div>

<h3>4. Test: Tulis File ke public/storage/avatars/</h3>
<div class="box">
<?php
if (!is_dir($avatarsPath)) {
    echo "<span class='err'>❌ Folder avatars tidak ada, tidak bisa test tulis.</span>";
} else {
    $testFile = $avatarsPath . '/test_write_' . time() . '.txt';
    $r = @file_put_contents($testFile, 'test_' . time());
    if ($r !== false) {
        echo "<span class='ok'>✅ file_put_contents() BERHASIL menulis file!</span><br>";
        echo "File: $testFile<br>";
        @unlink($testFile);
        echo "<span class='ok'>✅ unlink() BERHASIL menghapus file test.</span>";
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        echo "<span class='err'>❌ file_put_contents() GAGAL: $err</span><br>";
        echo "<span class='warn'>→ Masalah permission! Coba chmod 775 folder ini via File Manager.</span>";
    }
}
?>
</div>

<h3>5. Test: Simulasi Simpan Gambar (Base64 → File)</h3>
<div class="box">
<?php
// PNG 1x1 pixel transparan
$testImageB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
$imgData  = base64_decode($testImageB64);
$testName = 'test_avatar_' . time() . '.png';
$testFile = $avatarsPath . '/' . $testName;

if (!is_dir($avatarsPath)) {
    echo "<span class='err'>❌ Folder avatars tidak ada.</span>";
} elseif ($imgData === false) {
    echo "<span class='err'>❌ Gagal decode base64.</span>";
} else {
    $r = @file_put_contents($testFile, $imgData);
    if ($r !== false) {
        $url = '/storage/avatars/' . $testName;
        echo "<span class='ok'>✅ Gambar berhasil disimpan!</span><br>";
        echo "Path: $testFile<br>";
        echo "URL: <a href='$url' target='_blank'>$url</a> (klik untuk cek apakah bisa diakses)<br>";
        echo "<br><b>Apakah gambar terlihat di bawah ini?</b><br>";
        echo "<img src='$url' style='border:2px solid #22c55e;margin-top:8px;' onerror=\"this.outerHTML='<span class=err>❌ Gambar tidak bisa diakses via URL meski tersimpan di server.</span>'\">";
        // Hapus setelah 60 detik tidak bisa, biarkan user lihat
    } else {
        $err = error_get_last()['message'] ?? 'unknown';
        echo "<span class='err'>❌ GAGAL menyimpan gambar: $err</span>";
    }
}
?>
</div>

<h3>6. PHP Functions & Konfigurasi</h3>
<div class="box">
<?php
$funcs = ['file_put_contents', 'mkdir', 'unlink', 'copy', 'move_uploaded_file', 'symlink', 'chmod'];
foreach ($funcs as $f) {
    $ok = isFuncAvailable($f);
    echo sprintf(
        "<span class='%s badge badge-%s'>%s %s</span> &nbsp;",
        $ok ? 'ok' : 'err',
        $ok ? 'ok' : 'err',
        $ok ? '✅' : '❌',
        $f
    );
}
echo "<br><br>";
echo "<b>upload_max_filesize:</b> " . ini_get('upload_max_filesize') . "<br>";
echo "<b>post_max_size:</b> " . ini_get('post_max_size') . "<br>";
echo "<b>file_uploads:</b> " . (ini_get('file_uploads') ? '<span class="ok">ON</span>' : '<span class="err">OFF</span>') . "<br>";
echo "<b>open_basedir:</b> " . (ini_get('open_basedir') ?: '<span class="ok">(tidak dibatasi)</span>') . "<br>";
echo "<b>disable_functions:</b> " . (ini_get('disable_functions') ?: '<span class="ok">(tidak ada)</span>') . "<br>";
?>
</div>

<p style="color:#475569;font-size:11px;margin-top:32px;border-top:1px solid #1e293b;padding-top:16px;">
    ⚠️ <b>Hapus file ini setelah selesai!</b> Path: <code><?= __FILE__ ?></code>
</p>
</body>
</html>
