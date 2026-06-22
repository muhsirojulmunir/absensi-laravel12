<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$att = App\Models\Attendance::whereNotNull('check_in')->first();
if ($att) {
    echo "Check In Value: " . $att->check_in . "\n";
    echo "Length: " . strlen($att->check_in) . "\n";
} else {
    echo "No attendance record found.\n";
}
