<?php
// Script lokal untuk kompres vendor dengan forward slashes
$zipFile = __DIR__ . '/public/vendor_full.zip';
if (file_exists($zipFile)) {
    unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat zip");
}

$vendorPath = realpath(__DIR__ . '/vendor');
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ($iterator as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = 'vendor/' . substr($filePath, strlen($vendorPath) + 1);
        $relativePath = str_replace('\\', '/', $relativePath); // KUNCI: Forward slash
        $zip->addFile($filePath, $relativePath);
        $count++;
    }
}
$zip->close();
echo "Berhasil kompres $count file ke vendor_full.zip dengan forward slashes.\n";
