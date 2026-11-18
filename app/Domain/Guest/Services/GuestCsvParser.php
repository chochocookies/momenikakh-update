<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Guest\Data\ImportReport;

/**
 * GST-01: parses a guest CSV (comma, semicolon or tab; UTF-8 or Windows-1252) into a validated report.
 * Pure PHP, no database. Spreadsheet formula characters are NOT altered here; they are neutralised on export.
 */
final class GuestCsvParser
{
    private const ALIASES = [
        'name' => ['nama', 'name', 'namatamu', 'namalengkap', 'tamu', 'guest', 'guestname'],
        'group' => ['kelompok', 'grup', 'group', 'kategori', 'category'],
        'phone' => ['telepon', 'telp', 'tlp', 'hp', 'nohp', 'nomorhp', 'wa', 'nowa', 'whatsapp', 'phone', 'notelepon', 'nomortelepon', 'nomorwhatsapp'],
    ];

    public function __construct(
        private readonly PhoneNormalizer $phones,
        private readonly int $maxRows = 500,
        private readonly int $maxBytes = 524288,
        private readonly int $nameMax = 100,
        private readonly int $groupMax = 60,
    ) {}

    public function parse(string $path): ImportReport
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ImportReport::fatal('Berkas tidak dapat dibaca.');
        }
        $size = filesize($path);
        if ($size === 0) {
            return ImportReport::fatal('Berkas kosong.');
        }
        if ($size > $this->maxBytes) {
            return ImportReport::fatal(sprintf('Berkas terlalu besar (maksimal %d KB).', intdiv($this->maxBytes, 1024)));
        }

        $raw = $this->toUtf8((string) file_get_contents($path));
        // Checked AFTER decoding: UTF-16 text legitimately contains NUL bytes, a binary file still does once decoded.
        if (str_contains($raw, "\0")) {
            return ImportReport::fatal('Berkas bukan CSV teks yang valid.');
        }

        $delimiter = $this->detectDelimiter($raw);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $raw);
        rewind($stream);

        $header = $this->readRow($stream, $delimiter);
        if ($header === null) {
            fclose($stream);

            return ImportReport::fatal('Berkas kosong.');
        }
        $columns = $this->mapColumns($header);
        if (! isset($columns['name'])) {
            fclose($stream);

            return ImportReport::fatal('Kolom "nama" tidak ditemukan. Baris pertama harus berisi judul kolom, misalnya: nama, kelompok, telepon.');
        }

        $report = new ImportReport;
        $seen = [];
        $line = 1;
        while (($row = $this->readRow($stream, $delimiter)) !== null) {
            $line++;
            if ($this->isEmpty($row)) {
                continue;
            }
            $report->totalRows++;
            if ($report->totalRows > $this->maxRows) {
                fclose($stream);

                return ImportReport::fatal(sprintf('Terlalu banyak baris (maksimal %d tamu per impor).', $this->maxRows));
            }

            $name = $this->clean($row[$columns['name']] ?? '');
            $group = isset($columns['group']) ? $this->clean($row[$columns['group']] ?? '') : '';
            $phoneRaw = isset($columns['phone']) ? trim((string) ($row[$columns['phone']] ?? '')) : '';

            $rowErrors = [];
            if ($name === '') {
                $rowErrors[] = ['line' => $line, 'field' => 'name', 'message' => 'Nama wajib diisi.'];
            } elseif (mb_strlen($name) > $this->nameMax) {
                $rowErrors[] = ['line' => $line, 'field' => 'name', 'message' => "Nama terlalu panjang (maksimal {$this->nameMax} karakter)."];
            }
            if (mb_strlen($group) > $this->groupMax) {
                $rowErrors[] = ['line' => $line, 'field' => 'group', 'message' => "Kelompok terlalu panjang (maksimal {$this->groupMax} karakter)."];
            }
            $phone = null;
            if ($phoneRaw !== '') {
                $phone = $this->phones->normalize($phoneRaw);
                if ($phone === null) {
                    $rowErrors[] = ['line' => $line, 'field' => 'phone', 'message' => 'Nomor telepon tidak valid. Tulis sebagai teks, misalnya 081234567890.'];
                }
            }
            if ($rowErrors !== []) {
                array_push($report->errors, ...$rowErrors);

                continue;
            }

            $key = mb_strtolower($name).'|'.($phone ?? '');
            if (isset($seen[$key])) {
                $report->duplicates[] = $line;

                continue;
            }
            $seen[$key] = true;
            $report->valid[] = ['line' => $line, 'name' => $name, 'group' => $group === '' ? null : $group, 'phone' => $phone];
        }
        fclose($stream);

        if ($report->totalRows === 0) {
            return ImportReport::fatal('Tidak ada baris tamu di berkas.');
        }

        return $report;
    }

    private function toUtf8(string $raw): string
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $raw = (string) mb_convert_encoding($raw, 'UTF-8', 'UTF-16');
        } elseif (! mb_check_encoding($raw, 'UTF-8')) {
            $raw = (string) mb_convert_encoding($raw, 'UTF-8', 'Windows-1252'); // typical of older Excel exports
        }

        return str_replace(["\r\n", "\r"], "\n", $raw);
    }

    private function detectDelimiter(string $raw): string
    {
        $firstLine = strtok($raw, "\n") ?: '';
        $best = ',';
        $max = -1;
        foreach ([',', ';', "\t"] as $d) {
            $count = substr_count($firstLine, $d);
            if ($count > $max) {
                $max = $count;
                $best = $d;
            }
        }

        return $best;
    }

    /** @param resource $stream @return list<string|null>|null */
    private function readRow($stream, string $delimiter): ?array
    {
        $row = fgetcsv($stream, 0, $delimiter, '"', '');

        return $row === false ? null : $row;
    }

    /** @param list<string|null> $header @return array<string,int> */
    private function mapColumns(array $header): array
    {
        $map = [];
        foreach ($header as $i => $title) {
            $key = preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $title)) ?? '';
            foreach (self::ALIASES as $field => $aliases) {
                if (! isset($map[$field]) && in_array($key, $aliases, true)) {
                    $map[$field] = $i;
                }
            }
        }

        return $map;
    }

    /** @param list<string|null> $row */
    private function isEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function clean(string $value): string
    {
        return TextCleaner::singleLine($value);
    }
}
