<?php
/**
 * Debug Import Excel — Laporan isi file yang diupload
 * ⚠️ HAPUS SETELAH SELESAI!
 * Akses: https://yourdomain.com/debug_excel.php?key=dbg2024
 * Upload file Excel via form di bawah ini.
 */

$key = $_GET['key'] ?? '';
if ($key !== 'dbg2024') {
    die('Access denied.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Debug Excel Import</title>
<style>
body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 24px; }
h2 { color: #60a5fa; }
table { border-collapse: collapse; margin-top: 12px; font-size: 11px; width: 100%; }
th { background: #1e3a5f; color: #93c5fd; padding: 4px 8px; border: 1px solid #334155; text-align: left; }
td { padding: 3px 8px; border: 1px solid #334155; }
tr:nth-child(even) { background: #1e293b; }
.highlight { background: #7c3aed !important; color: #fff; }
.info { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 12px 16px; margin: 12px 0; font-size: 12px; }
code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
</style>
</head>
<body>
<h2>🔍 Debug: Excel Import Reader</h2>

<form method="POST" enctype="multipart/form-data" style="margin-bottom:24px;">
    <input type="hidden" name="key_post" value="dbg2024">
    <label style="color:#93c5fd;font-size:13px;">Upload file Excel (.xlsx / .xls / .csv):</label><br><br>
    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv,.ods" required style="color:#e2e8f0;margin-bottom:12px;"><br>
    <button type="submit" style="background:#7c3aed;color:#fff;padding:8px 20px;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
        🔍 Analisa File
    </button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['key_post'] ?? '') === 'dbg2024' && isset($_FILES['excel_file'])) {
    $uploadedFile = $_FILES['excel_file'];
    $tmpPath      = $uploadedFile['tmp_name'];
    $originalName = $uploadedFile['name'];
    $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    echo "<div class='info'><b>Nama File:</b> " . htmlspecialchars($originalName) . " | <b>Ext:</b> $ext | <b>Size:</b> " . number_format($uploadedFile['size']) . " bytes</div>";

    // Bootstrap autoloader Laravel
    $root = dirname(__DIR__);
    $autoload = $root . '/vendor/autoload.php';

    if (!file_exists($autoload)) {
        die('<div class="info" style="color:#f87171;">❌ vendor/autoload.php tidak ditemukan. Pastikan Composer sudah dijalankan.</div>');
    }

    require_once $autoload;

    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, false, false);

        $totalRows = count($rows);
        echo "<div class='info'>✅ Berhasil dibaca. <b>Total baris: $totalRows</b></div>";

        if (empty($rows)) {
            echo "<div class='info' style='color:#f87171;'>⚠️ Tidak ada baris sama sekali!</div>";
        } else {
            // Coba deteksi header
            $colKode = $colNama = $colWarna = $colSize = $colQty = null;
            $headerRowIndex = null;

            foreach ($rows as $ri => $row) {
                foreach ($row as $ci => $cell) {
                    $val = strtolower(trim((string)$cell));
                    if ($colKode === null && strpos($val, 'kode') !== false) {
                        $colKode = $ci; $headerRowIndex = $ri;
                    }
                    if ($colNama === null && strpos($val, 'nama') !== false) $colNama = $ci;
                    if ($colWarna === null && strpos($val, 'warna') !== false) $colWarna = $ci;
                    if ($colSize === null && (strpos($val, 'size') !== false || strpos($val, 'ukuran') !== false)) $colSize = $ci;
                    if ($colQty === null && (strpos($val, 'qty') !== false || strpos($val, 'quantity') !== false)) $colQty = $ci;
                    if ($colKode !== null && $colQty !== null) break;
                }
                if ($colKode !== null && $colQty !== null) break;
            }

            echo "<div class='info'>";
            echo "<b>Hasil Deteksi Kolom Otomatis:</b><br>";
            echo "Header Row Index: <code>" . ($headerRowIndex ?? 'TIDAK DITEMUKAN') . "</code><br>";
            echo "colKode: <code>" . ($colKode ?? 'null — fallback ke 2') . "</code><br>";
            echo "colNama: <code>" . ($colNama ?? 'null — fallback ke 5') . "</code><br>";
            echo "colWarna: <code>" . ($colWarna ?? 'null') . "</code><br>";
            echo "colSize: <code>" . ($colSize ?? 'null') . "</code><br>";
            echo "colQty: <code>" . ($colQty ?? 'null — fallback ke 7') . "</code><br>";
            echo "</div>";

            // Simulasi parsing
            $colKodeSim = $colKode ?? 2;
            $colNamaSim = $colNama ?? 5;
            $colQtySim  = $colQty  ?? 7;
            $hdrSim     = $headerRowIndex ?? 0;

            $simulasiCount = 0;
            $skippedCount  = 0;
            $skipReasons   = [];

            echo "<div class='info'><b>Simulasi Parsing (mode: add):</b></div>";
            echo "<table>";
            echo "<tr>
                <th>#</th>
                <th>Row Excel</th>
                <th>kodeBarang (col{$colKodeSim})</th>
                <th>namaBarang (col{$colNamaSim})</th>
                <th>qtyRaw (col{$colQtySim})</th>
                <th>qty Parsed</th>
                <th>Status</th>
            </tr>";

            for ($i = $hdrSim + 1; $i < min($totalRows, $hdrSim + 31); $i++) {
                $row = $rows[$i];

                $kodeBarang = isset($row[$colKodeSim]) ? trim((string)$row[$colKodeSim]) : '';
                $namaBarang = isset($row[$colNamaSim]) ? trim((string)$row[$colNamaSim]) : '';
                $qtyRaw     = isset($row[$colQtySim])  ? trim((string)$row[$colQtySim])  : '';

                $status = '✅ OK';
                $rowClass = '';
                if (empty($kodeBarang) || empty($namaBarang)) {
                    $status = '⛔ SKIP: kode/nama kosong';
                    $rowClass = '';
                    $skippedCount++;
                } elseif (!preg_match('/^\d+$/', $kodeBarang)) {
                    $status = "⛔ SKIP: kode tidak murni angka → <code>" . htmlspecialchars($kodeBarang) . "</code>";
                    $skippedCount++;
                    $skipReasons[] = "Baris $i: kode='$kodeBarang' tidak murni angka";
                } elseif (stripos($namaBarang, 'ACCOS') !== false) {
                    $status = '⛔ SKIP: namaBarang mengandung ACCOS';
                    $skippedCount++;
                } else {
                    $simulasiCount++;
                    $rowClass = 'highlight';
                }

                $qty = 0;
                if (preg_match('/(-?\d+(\.\d+)?)/', $qtyRaw, $matches)) {
                    $qty = (int)round((float)$matches[1]);
                }

                echo "<tr class='$rowClass'>
                    <td>$i</td>
                    <td>" . htmlspecialchars(implode(' | ', array_map('strval', array_slice($row, 0, 5)))) . "</td>
                    <td><b>" . htmlspecialchars($kodeBarang) . "</b></td>
                    <td>" . htmlspecialchars($namaBarang) . "</td>
                    <td>" . htmlspecialchars($qtyRaw) . "</td>
                    <td><b>$qty</b></td>
                    <td>$status</td>
                </tr>";
            }
            echo "</table>";

            echo "<div class='info'>";
            echo "✅ <b>Akan diimport: $simulasiCount baris</b><br>";
            echo "⛔ <b>Dilewati: $skippedCount baris</b><br>";
            if (!empty($skipReasons)) {
                echo "<br><b>Contoh penyebab skip:</b><br>" . implode('<br>', array_map('htmlspecialchars', array_slice($skipReasons, 0, 10)));
            }
            echo "</div>";

            // Tampilkan 15 baris mentah pertama dari Excel
            echo "<h3 style='color:#60a5fa;margin-top:24px;'>📋 15 Baris Pertama Mentah dari Excel:</h3>";
            echo "<table><tr><th>Row</th>";
            $maxCols = max(array_map('count', array_slice($rows, 0, 15)));
            for ($c = 0; $c < $maxCols; $c++) echo "<th>Col[$c]</th>";
            echo "</tr>";
            for ($i = 0; $i < min(15, $totalRows); $i++) {
                echo "<tr><td><b>$i</b></td>";
                for ($c = 0; $c < $maxCols; $c++) {
                    $val = $rows[$i][$c] ?? '';
                    echo "<td>" . htmlspecialchars((string)$val) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }

    } catch (\Throwable $e) {
        echo "<div class='info' style='color:#f87171;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<pre style='color:#fca5a5;font-size:11px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
?>
<p style="color:#475569;font-size:11px;margin-top:32px;">⚠️ Hapus file ini setelah selesai: <code><?= __FILE__ ?></code></p>
</body>
</html>
