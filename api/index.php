<?php

// Show errors for debugging
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Set proper paths for Vercel serverless environment
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Change working directory to Laravel root
chdir(__DIR__ . '/..');

// Check if vendor/autoload.php exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo json_encode([
        'error' => 'vendor/autoload.php not found',
        'message' => 'Composer dependencies are not installed. The vercel-php runtime should handle this automatically.',
        'cwd' => getcwd(),
        'dir' => __DIR__,
    ]);
    exit(1);
}

// Forward request to Laravel
require __DIR__ . '/../public/index.php';
