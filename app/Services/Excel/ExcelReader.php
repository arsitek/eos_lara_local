<?php

namespace App\Services\Excel;

use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    /**
     * Read an Excel file from storage and return it as a collection of rows.
     *
     * @param  string  $relativePath  Path relative to storage/app (e.g. "privatee/SUKPA 2026.xlsx").
     * @param  bool    $withHeadingRow Whether the first row should be treated as headings.
     * @return \Illuminate\Support\Collection<array>
     */
    public function read(string $relativePath, bool $withHeadingRow = true): Collection {
        $fullPath = storage_path('app/' . ltrim($relativePath, '/'));

        if (! file_exists($fullPath)) {
            throw new \RuntimeException("Excel file not found at {$fullPath}");
        }

        $reader = new FastExcel();

        if (! $withHeadingRow) {
            $reader = $reader->withoutHeaders();
        }

        // FastExcel returns a collection; wrap in collect() for consistency
        return collect($reader->import($fullPath));
    }

    /**
     * Read a specific sheet from an Excel file by sheet name.
     *
     * @param  string  $relativePath  Path relative to storage/app.
     * @param  string  $sheetName     Sheet name to read (e.g. "PPK").
     * @param  bool    $withHeadingRow Treat first row as header keys when true.
     * @return \Illuminate\Support\Collection<array>
     */
    public function readSheet(string $relativePath, string $sheetName, bool $withHeadingRow = true) : Collection {
        $fullPath = storage_path('app/' . ltrim($relativePath, '/'));

        if (! file_exists($fullPath)) {
            throw new \RuntimeException("Excel file not found at {$fullPath}");
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (! $sheet) {
            throw new \RuntimeException("Sheet '{$sheetName}' not found in {$relativePath}");
        }

        $rows = $sheet->toArray(null, true, true, true); // keep values as strings, preserve column letters as keys

        if (empty($rows)) {
            return collect();
        }

        if (! $withHeadingRow) {
            // Drop column letters, keep positional arrays
            return collect(array_map('array_values', $rows));
        }

        // Build safe headers with fallbacks to column letters and ensure uniqueness
        $headerRow = array_shift($rows); // still keyed by column letters
        $headers = [];
        foreach ($headerRow as $colLetter => $value) {
            $base = trim((string) ($value ?? ''));
            $header = $base !== '' ? $base : $colLetter; // fallback to column letter

            // ensure unique header names
            $original = $header;
            $suffix = 1;
            while (in_array($header, $headers, true)) {
                $suffix++;
                $header = $original . '_' . $suffix;
            }

            $headers[] = $header;
        }

        $headerIndex = array_keys($headerRow); // preserve column order

        return collect($rows)->map(function ($row) use ($headers, $headerIndex) {
            $values = [];
            foreach ($headerIndex as $i => $colLetter) {
                $values[$i] = $row[$colLetter] ?? null;
            }
            return array_combine($headers, $values);
        });
    }
}
