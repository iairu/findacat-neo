<?php

namespace App\Legacy;

use InvalidArgumentException;

/**
 * Reads CSV exported by the old Find A Cat backup page into header-keyed rows.
 *
 * Rows are matched to the header by name, so column order does not matter. One known export
 * defect is repaired: the old cats export wrote a `genetic_tests_file` header but no value.
 */
final class CsvReader
{
    /**
     * @return list<array<string, string>>
     */
    public static function read(string $path, ImportReport $report): array
    {
        $handle = @fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidArgumentException("Cannot open {$path}");
        }

        $header = fgetcsv($handle, escape: '');
        if ($header === false || $header === [null]) {
            throw new InvalidArgumentException("{$path} is empty");
        }
        // Strip a UTF-8 byte order mark that spreadsheet tools add.
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $rows = [];
        $line = 1;
        while (($values = fgetcsv($handle, escape: '')) !== false) {
            $line++;
            if ($values === [null]) {
                continue; // blank line
            }
            $values = self::align($header, $values, $line, $report);
            $rows[] = array_combine($header, $values);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<string>  $header
     * @param  list<string|null>  $values
     * @return list<string|null>
     */
    private static function align(array $header, array $values, int $line, ImportReport $report): array
    {
        $missing = count($header) - count($values);
        if ($missing === 1 && ($at = array_search('genetic_tests_file', $header, true)) !== false) {
            array_splice($values, $at, 0, [null]);

            return $values;
        }
        if ($missing !== 0) {
            $report->warn("Line {$line}: expected ".count($header).' columns, found '.count($values).'; padded or truncated.');
        }

        return array_slice(array_pad($values, count($header), null), 0, count($header));
    }
}
