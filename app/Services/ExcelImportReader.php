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
     * Untuk Excel 97-2003 (.xls): menggunakan formatData=true agar nilai
     * yang terformat (seperti "-1.00 PSG") terbaca dengan benar.
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

        // XLS lama: baca format cell agar nilai string terbaca sesuai tampilan Excel
        $reader->setReadDataOnly(!$isLegacyXls);

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        // toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef)
        $rows = $sheet->toArray(null, true, $isLegacyXls, false);

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

