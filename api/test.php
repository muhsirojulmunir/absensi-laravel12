<?php

header('Content-Type: application/json');

echo json_encode([
    'status' => 'OK',
    'php_version' => phpversion(),
    'message' => 'PHP berhasil berjalan di Vercel!',
    'extensions' => get_loaded_extensions(),
    'vendor_exists' => file_exists(__DIR__ . '/../vendor/autoload.php'),
    'composer_exists' => file_exists(__DIR__ . '/../composer.json'),
    'public_exists' => file_exists(__DIR__ . '/../public/index.php'),
], JSON_PRETTY_PRINT);
