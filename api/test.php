<?php

header('Content-Type: application/json');

$checks = [
    'php_version' => phpversion(),
    'vendor_exists' => file_exists(__DIR__ . '/../vendor/autoload.php'),
    'composer_exists' => file_exists(__DIR__ . '/../composer.json'),
    'public_index_exists' => file_exists(__DIR__ . '/../public/index.php'),
    'bootstrap_app_exists' => file_exists(__DIR__ . '/../bootstrap/app.php'),
    'env_file_exists' => file_exists(__DIR__ . '/../.env'),
    'storage_dir_exists' => is_dir(__DIR__ . '/../storage'),
    'storage_writable' => is_writable(__DIR__ . '/../storage'),
    'cwd' => getcwd(),
    'dir' => __DIR__,
    'app_key' => getenv('APP_KEY') ?: 'NOT SET',
];

echo json_encode($checks, JSON_PRETTY_PRINT);
