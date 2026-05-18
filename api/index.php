<?php

// Show errors for debugging
ini_set('display_errors', '1');
error_reporting(E_ALL);

// In Vercel serverless, the filesystem is read-only except /tmp
// We need to redirect Laravel's writable paths to /tmp

$storagePath = '/tmp/storage';
$dirs = [
    $storagePath,
    $storagePath . '/app',
    $storagePath . '/app/public',
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Set environment variables for Laravel
$_ENV['APP_STORAGE'] = $storagePath;
$_SERVER['APP_STORAGE'] = $storagePath;
putenv("APP_STORAGE={$storagePath}");

// Set proper paths for Vercel serverless environment
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Change working directory to Laravel root
chdir(__DIR__ . '/..');

// Forward request to Laravel
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    echo "<h1>CRITICAL ERROR:</h1>";
    echo "<b>" . $e->getMessage() . "</b><br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
