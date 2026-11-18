<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

/** Single-line text hygiene for guest names and groups: strips control characters, zero-width space, word joiners, BOM and bidi overrides. Keeps ZWJ/ZWNJ and LRM/RLM, which real names and emoji sequences need. */
final class TextCleaner
{
    public static function singleLine(string $value): string
    {
        $value = preg_replace('/[\p{Cc}\x{200B}\x{2060}-\x{2064}\x{2066}-\x{2069}\x{202A}-\x{202E}\x{FEFF}]+/u', ' ', $value) ?? $value;

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
