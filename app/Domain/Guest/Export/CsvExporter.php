<?php

declare(strict_types=1);

namespace App\Domain\Guest\Export;

/**
 * GST-03: CSV writer that neutralises spreadsheet formula injection (CSV injection, OWASP).
 * A text cell that starts with = + - @ (also after leading whitespace) or a tab/CR is prefixed with a
 * single quote so Excel, LibreOffice and Google Sheets treat it as text. Numbers (int/float) are left alone.
 */
final class CsvExporter
{
    public function __construct(private readonly string $delimiter = ',') {}

    public static function neutralize(string|int|float|bool|null $value): string|int|float
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'ya' : 'tidak';
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if ($value === '') {
            return '';
        }
        if (preg_match('/^[\t\r\n\0 ]*[=+\-@]/', $value) === 1 || $value[0] === "\t" || $value[0] === "\r") {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Writes the header and every row to an open stream, preceded by a UTF-8 BOM so Excel reads accents correctly.
     *
     * @param  resource  $handle
     * @param  list<string>  $headers
     * @param  iterable<list<string|int|float|bool|null>>  $rows
     */
    public function write($handle, array $headers, iterable $rows): int
    {
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, array_map(fn ($h) => self::neutralize($h), $headers), $this->delimiter, '"', '', "\r\n");
        $count = 0;
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($c) => self::neutralize($c), $row), $this->delimiter, '"', '', "\r\n");
            $count++;
        }

        return $count;
    }

    /** @param list<string> $headers @param iterable<list<string|int|float|bool|null>> $rows */
    public function toString(array $headers, iterable $rows): string
    {
        $h = fopen('php://temp', 'r+');
        $this->write($h, $headers, $rows);
        rewind($h);
        $out = (string) stream_get_contents($h);
        fclose($h);

        return $out;
    }
}
