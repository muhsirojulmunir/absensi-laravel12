<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo json_encode(\App\Models\User::whereHas('role', function($q) {
    $q->where('slug', 'super-admin');
})->get(['id', 'name', 'email'])->toArray());
