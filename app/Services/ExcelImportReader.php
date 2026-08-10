<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportReader
{
    /**
     * Baca file Excel apapun formatnya (xlsx, xls, xlsb, ods, csv) dan
     * kembalikan array baris (0-indexed) mirip SimpleXLSX::rows().
     *
     * Untuk Excel 97-2003 (.xls): menggunakan formatData=true agar nilai
     * yang terformat (seperti tanggal, angka desimal) terbaca dengan benar.
     */
    public static function readRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);

        // Untuk XLS lama, matikan data-only agar format cell terbaca benar
        $readerType = IOFactory::identify($path);
        $isLegacyXls = in_array($readerType, ['Xls', 'Slk', 'Gnumeric']);

        $reader->setReadDataOnly(!$isLegacyXls);

        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        // toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef)
        // Untuk XLS lama: formatData=true agar angka & string terbaca sesuai tampilan Excel
        $rows = $sheet->toArray(null, true, $isLegacyXls, false);

        // Normalisasi encoding: XLS lama kadang CP1252 atau Windows-1252
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
