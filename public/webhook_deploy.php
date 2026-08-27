<?php
/**
 * ============================================================
 *  GitHub Webhook — Auto Deploy untuk Dewaweb cPanel
 * ============================================================
 *  Cara pakai:
 *  1. Push file ini ke GitHub → hosting akan punya file ini
 *  2. Di GitHub repo → Settings → Webhooks → Add webhook:
 *       Payload URL : https://absensi.recordshoes.com/webhook_deploy.php
 *       Content type: application/json
 *       Secret      : (isi dengan WEBHOOK_SECRET di bawah, misal: rahasia_deploy_2024)
 *       Event       : Just the push event
 *  3. Simpan. Setiap git push ke branch main → server auto deploy!
 * ============================================================
 */

// ── KONFIGURASI ──────────────────────────────────────────────
// Ganti nilai ini sesuai kebutuhan
const WEBHOOK_SECRET  = 'rahasia_deploy_2024';   // harus sama dengan GitHub Webhook Secret
const DEPLOY_BRANCH   = 'refs/heads/main';       // branch yang di-watch
const APP_ROOT        = '/home/recordsh/absensi.recordshoes.com';
const REPO_ROOT       = '/home/recordsh/repositories/absensi-laravel12';
const PHP_BIN         = '/usr/local/bin/php';
const LOG_FILE        = '/home/recordsh/deploy.log';
// ─────────────────────────────────────────────────────────────

header('Content-Type: application/json');

// 1. Verifikasi signature dari GitHub
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (empty($signature)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No signature']);
    exit;
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// 2. Decode payload dan cek branch
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';

if ($ref !== DEPLOY_BRANCH) {
    echo json_encode(['status' => 'skipped', 'message' => "Branch $ref diabaikan"]);
    exit;
}

// 3. Jalankan deploy
$timestamp   = date('Y-m-d H:i:s');
$commit      = $data['after'] ?? 'unknown';
$commitMsg   = $data['head_commit']['message'] ?? '-';
$pusher      = $data['pusher']['name'] ?? 'unknown';

$logHeader = "\n========================================\n";
$logHeader .= "[$timestamp] Deploy triggered\n";
$logHeader .= "Commit : $commit\n";
$logHeader .= "Message: $commitMsg\n";
$logHeader .= "Pusher : $pusher\n";
$logHeader .= "========================================\n";

file_put_contents(LOG_FILE, $logHeader, FILE_APPEND);

// Perintah deploy — sesuai alur cPanel Git Version Control
$commands = [
    // Pull dari GitHub ke repo cPanel
    "cd " . REPO_ROOT . " && git fetch --all 2>&1",
    "cd " . REPO_ROOT . " && git reset --hard origin/main 2>&1",

    // Copy ke folder aplikasi (sesuai .cpanel.yml)
    "cp -R " . REPO_ROOT . "/. " . APP_ROOT . "/ 2>&1",

    // Jalankan artisan commands
    PHP_BIN . " " . APP_ROOT . "/artisan migrate --force 2>&1",
    PHP_BIN . " " . APP_ROOT . "/artisan config:cache 2>&1",
    PHP_BIN . " " . APP_ROOT . "/artisan route:cache 2>&1",
    PHP_BIN . " " . APP_ROOT . "/artisan view:cache 2>&1",

    // Bersihkan cache storage/framework
    "find " . APP_ROOT . "/storage/framework/views -name '*.php' -delete 2>&1",
];

$output  = [];
$success = true;

foreach ($commands as $cmd) {
    $result = shell_exec($cmd);
    $line   = "[CMD] $cmd\n[OUT] " . trim($result ?? '(no output)') . "\n";
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
    $output[] = ['cmd' => $cmd, 'out' => trim($result ?? '')];
}

file_put_contents(LOG_FILE, "[DONE] Deploy selesai pada $timestamp\n", FILE_APPEND);

http_response_code(200);
echo json_encode([
    'status'  => 'ok',
    'message' => 'Deploy berhasil',
    'commit'  => $commit,
    'pusher'  => $pusher,
]);
