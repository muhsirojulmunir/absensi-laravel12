<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Ods;

class ExcelImportReader
{
    /**
     * Baca file Excel apapun formatnya (xlsx, xls, ods, csv) dan
     * kembalikan array baris (0-indexed) mirip SimpleXLSX::rows().
     *
     * CATATAN PENTING soal memori:
     * - setReadDataOnly(true) dipakai untuk SEMUA format supaya PhpSpreadsheet TIDAK
     *   memuat info style/border/format ke memori, hanya isi cell-nya saja. Nilai qty
     *   di export Ramayana (mis. "-1.00 PSG") sudah tersimpan sebagai TEKS APA ADANYA
     *   di dalam cell (bukan angka dengan format tampilan), jadi tetap terbaca persis
     *   sama walau style tidak dimuat.
     * - Export .xls lama sering punya ratusan kolom "kosong" yang sebenarnya cuma
     *   berisi border/format tanpa nilai (highestColumn bisa sampai kolom ke-257!).
     *   Kalau semua itu ikut dimuat, penggunaan memori bisa 10x lipat lebih besar dan
     *   menyebabkan proses gagal/crash (500) di server dengan limit memori kecil.
     *   Makanya kita batasi pembacaan hanya sampai getHighestDataColumn()/Row() —
     *   kolom & baris yang BENAR-BENAR berisi data.
     *
     * CATATAN: Kita tidak pakai IOFactory::identify() karena PHP upload
     * menyimpan file tanpa ekstensi di /tmp sehingga identify() bisa crash.
     * Gunakan ekstensi original dari nama file asli.
     */
    public static function readRows(string $path, string $originalExtension = ''): array
    {
        $ext = strtolower($originalExtension ?: pathinfo($path, PATHINFO_EXTENSION));
        $isLegacyXls = ($ext === 'xls');

        // Pilih reader berdasarkan ekstensi agar tidak ada identify() yang crash
        switch ($ext) {
            case 'xls':
                $reader = new Xls();
                break;
            case 'ods':
                $reader = new Ods();
                break;
            case 'csv':
                $reader = new Csv();
                break;
            default: // xlsx, xlsm, xlst, dan format baru lainnya
                $reader = new Xlsx();
                break;
        }

        // Selalu skip info style/format — jauh lebih hemat memori, dan nilai cell
        // (termasuk teks berformat seperti "-1.00 PSG") tetap terbaca apa adanya.
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        // Batasi ke kolom/baris yang benar-benar berisi data (lihat catatan di atas).
        $highestDataColumn = $sheet->getHighestDataColumn();
        $highestDataRow    = $sheet->getHighestDataRow();
        $range = "A1:{$highestDataColumn}{$highestDataRow}";

        // rangeToArray($range, $nullValue, $calculateFormulas, $formatData, $returnCellRef)
        // formatData=false karena style/number-format tidak dimuat (readDataOnly=true).
        $rows = $sheet->rangeToArray($range, null, true, false, false);

        // Normalisasi encoding: XLS 97-2003 kadang Windows-1252
        if ($isLegacyXls) {
            foreach ($rows as &$row) {
                foreach ($row as &$cell) {
                    if (is_string($cell) && !mb_check_encoding($cell, 'UTF-8')) {
                        $cell = mb_convert_encoding($cell, 'UTF-8', 'Windows-1252');
                    }
                }
            }
            unset($row, $cell);
        }

        return $rows;
    }
}

