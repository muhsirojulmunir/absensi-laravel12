<?php
/**
 * Test import Excel lokal — jalankan di browser: http://absensi-laravel12.test/test_import_local.php
 * HAPUS SETELAH SELESAI
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(120);

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html><html><head><meta charset="utf-8">
<title>Test Import Lokal</title>
<style>
body { font-family: monospace; background:#0f172a; color:#e2e8f0; padding:24px; }
h2 { color:#60a5fa; } pre { background:#1e293b; padding:12px; border-radius:8px; font-size:12px; overflow:auto; }
.ok { color:#4ade80; } .err { color:#f87171; } .warn { color:#fbbf24; }
table { border-collapse:collapse; width:100%; font-size:11px; margin-top:12px; }
th { background:#1e3a5f; color:#93c5fd; padding:4px 8px; border:1px solid #334155; text-align:left; }
td { padding:3px 8px; border:1px solid #334155; }
tr:nth-child(even) { background:#1e293b; }
.hl { background:#7c3aed!important; color:#fff; }
</style></head><body>
<h2>🔍 Test Import Excel — Lokal</h2>

<?php
// Step 1: Cek PhpSpreadsheet tersedia
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    die('<p class="err">❌ vendor/autoload.php tidak ditemukan</p>');
}
require_once $autoload;

echo '<p class="ok">✅ Autoload OK</p>';

// Step 2: Cek class tersedia
if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
    die('<p class="err">❌ PhpSpreadsheet tidak terinstall. Jalankan: composer require phpoffice/phpspreadsheet</p>');
}
echo '<p class="ok">✅ PhpSpreadsheet tersedia: ' . \PhpOffice\PhpSpreadsheet\IOFactory::class . '</p>';

// Step 3: Upload form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $file = $_FILES['excel'];
    $tmpPath = $file['tmp_name'];
    $name    = $file['name'];
    $ext     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    echo "<h3 style='color:#60a5fa'>📊 Hasil Analisa: " . htmlspecialchars($name) . "</h3>";
    echo "<p>Ext: <b>$ext</b> | Size: <b>" . number_format($file['size']) . " bytes</b></p>";

    try {
        // Test identify
        $readerType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($tmpPath);
        echo "<p class='ok'>✅ Reader Type terdeteksi: <b>$readerType</b></p>";
        
        $isLegacyXls = in_array($readerType, ['Xls', 'Slk', 'Gnumeric']);
        echo "<p>" . ($isLegacyXls ? "<span class='warn'>⚠️ File XLS lama (97-2003) — mode formatData=true</span>" : "<span class='ok'>✅ File modern (xlsx/csv)</span>") . "</p>";

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpPath);
        $reader->setReadDataOnly(!$isLegacyXls);
        
        $spreadsheet = $reader->load($tmpPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, $isLegacyXls, false);
        
        $totalRows = count($rows);
        echo "<p class='ok'>✅ Berhasil dibaca. <b>Total baris: $totalRows</b></p>";

        if ($isLegacyXls) {
            // Fix encoding
            foreach ($rows as &$row) {
                foreach ($row as &$cell) {
                    if (is_string($cell) && !mb_check_encoding($cell, 'UTF-8')) {
                        $cell = mb_convert_encoding($cell, 'UTF-8', 'Windows-1252');
                    }
                }
            }
            unset($row, $cell);
            echo "<p class='ok'>✅ Encoding dinormalisasi (Windows-1252 → UTF-8)</p>";
        }

        // Deteksi header
        $kodeKeywords  = ['kode', 'artikel', 'barcode', 'article', 'product code', 'item code'];
        $namaKeywords  = ['nama', 'deskripsi', 'description', 'produk', 'barang', 'item'];
        $qtyKeywords   = ['qty', 'quantity', 'jumlah', 'stok', 'stock', 'total qty', 'saldo'];

        $colKode = $colNama = $colQty = null;
        $headerRowIndex = null;

        foreach ($rows as $ri => $row) {
            foreach ($row as $ci => $cell) {
                $val = strtolower(trim((string)$cell));
                if ($val === '') continue;
                foreach ($kodeKeywords as $kw) {
                    if (strpos($val, $kw) !== false) { $colKode = $ci; $headerRowIndex = $ri; break; }
                }
                foreach ($namaKeywords as $kw) {
                    if (strpos($val, $kw) !== false) { $colNama = $ci; break; }
                }
                foreach ($qtyKeywords as $kw) {
                    if (strpos($val, $kw) !== false) { $colQty = $ci; break; }
                }
            }
            if ($colKode !== null && $colQty !== null) break;
        }

        if ($colKode === null) $colKode = 0;
        if ($colNama === null) $colNama = 1;
        if ($colQty === null)  $colQty  = 2;
        if ($headerRowIndex === null) $headerRowIndex = 0;

        echo "<div style='background:#1e293b;border-radius:8px;padding:12px;margin:12px 0;font-size:12px;'>";
        echo "<b>Deteksi Kolom:</b> Kode=<b>$colKode</b> | Nama=<b>$colNama</b> | Qty=<b>$colQty</b> | HeaderRow=<b>$headerRowIndex</b><br>";
        echo "</div>";

        // Simulasi parsing
        $okCount      = 0;
        $skipCount    = 0;
        $skipReasons  = [];

        echo "<table><tr><th>#Row</th><th>Kode (col$colKode)</th><th>Nama (col$colNama)</th><th>QtyRaw (col$colQty)</th><th>QtyParsed</th><th>Status</th></tr>";

        for ($i = $headerRowIndex + 1; $i < min($totalRows, $headerRowIndex + 51); $i++) {
            $row = $rows[$i];

            $kodeBarang = isset($row[$colKode]) ? trim((string)$row[$colKode]) : '';
            $namaBarang = isset($row[$colNama]) ? trim((string)$row[$colNama]) : '';
            $qtyCell    = $row[$colQty] ?? null;
            $qtyRaw     = trim((string)$qtyCell);

            $status   = '✅ OK';
            $rowClass = 'hl';

            if ($namaBarang === '') {
                $status = '⛔ SKIP: nama kosong';
                $rowClass = '';
                $skipCount++;
            } elseif (
                in_array(trim(strtolower($namaBarang)), ['total', 'grand total', 'subtotal', 'jumlah', 'total :', 'total:']) ||
                in_array(trim(strtolower($kodeBarang)), ['total', 'grand total', 'subtotal', 'jumlah']) ||
                stripos($namaBarang, 'ACCOS') !== false
            ) {
                $status = '⛔ SKIP: footer/total';
                $rowClass = '';
                $skipCount++;
            } elseif (mb_strlen($namaBarang) < 3) {
                $status = '⛔ SKIP: nama terlalu pendek';
                $rowClass = '';
                $skipCount++;
            } else {
                $isDashKode = ($kodeBarang === '-' || $kodeBarang === '' || $kodeBarang === '0');
                if (!$isDashKode) {
                    $kodeClean = preg_replace('/[\s\-_\.]+/', '', $kodeBarang);
                    if (strlen($kodeClean) < 3) {
                        $status = "⛔ SKIP: kode '$kodeBarang' tidak valid";
                        $rowClass = '';
                        $skipCount++;
                    } else {
                        $okCount++;
                    }
                } else {
                    $okCount++;
                }
            }

            $qty = 0;
            if (is_numeric($qtyCell) && $qtyCell !== null) {
                $qty = abs((int)round((float)$qtyCell));
            } elseif (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $m)) {
                $qty = abs((int)round((float)$m[1]));
            }

            echo "<tr class='$rowClass'>";
            echo "<td>$i</td>";
            echo "<td>" . htmlspecialchars($kodeBarang) . "</td>";
            echo "<td>" . htmlspecialchars($namaBarang) . "</td>";
            echo "<td>" . htmlspecialchars($qtyRaw) . "</td>";
            echo "<td><b>$qty</b></td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<p style='margin-top:12px;'><b class='ok'>✅ AKAN DIIMPORT: $okCount baris</b> | <b class='err'>⛔ DILEWATI: $skipCount baris</b></p>";

        // Tampilkan raw baris pertama
        echo "<h3 style='color:#60a5fa;margin-top:24px;'>📋 5 Baris Mentah Pertama:</h3><pre>";
        for ($i = 0; $i < min(5, $totalRows); $i++) {
            echo "Row[$i]: " . json_encode($rows[$i], JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "</pre>";

    } catch (\Throwable $e) {
        echo "<p class='err'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre class='err'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data" style="margin:20px 0;">
        <label style="color:#93c5fd">Upload file Excel Ramayana (.xls / .xlsx / .csv):</label><br><br>
        <input type="file" name="excel" accept=".xls,.xlsx,.csv,.ods" required style="color:#e2e8f0;margin-bottom:12px;"><br>
        <button type="submit" style="background:#7c3aed;color:#fff;padding:8px 20px;border:none;border-radius:8px;cursor:pointer;">
            🔍 Analisa & Simulasi Import
        </button>
    </form>';
}
?>
<p style="color:#475569;margin-top:32px;font-size:11px;">⚠️ Hapus file ini setelah selesai: <?= __FILE__ ?></p>
</body></html>
