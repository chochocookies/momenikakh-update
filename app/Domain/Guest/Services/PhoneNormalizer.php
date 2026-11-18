<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

/**
 * Normalises phone numbers to the digits-only international form wa.me expects (e.g. 6281234567890).
 * Returns null when the input is empty or cannot be a valid number.
 */
final class PhoneNormalizer
{
    public function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        // Excel often turns long numbers into scientific notation (8.12E+11): the digits are already lost.
        if (preg_match('/^\d+(?:[.,]\d+)?e[+-]?\d+$/i', $raw)) {
            return null;
        }

        $international = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if ($international) {
            $number = $digits;
        } elseif (str_starts_with($digits, '00')) {
            $number = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $number = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            $number = $digits;
        } elseif (str_starts_with($digits, '8')) {
            $number = '62'.$digits;
        } else {
            return null;
        }

        if (str_starts_with($number, '620')) {      // "+62 0812..." typed by mistake
            $number = '62'.substr($number, 3);
        }

        $len = strlen($number);
        if ($len < 9 || $len > 15 || $number[0] === '0') {
            return null;
        }
        if (str_starts_with($number, '62') && ($len < 10 || $len > 14)) {
            return null;
        }

        return $number;
    }
}
