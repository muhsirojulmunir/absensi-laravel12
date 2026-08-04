<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportReader
{
    /**
     * Baca file Excel apapun formatnya (xlsx, xls, xlsb, ods, csv) dan
     * kembalikan array baris (0-indexed) mirip SimpleXLSX::rows().
     */
    public static function readRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, false, false);
    }
}
