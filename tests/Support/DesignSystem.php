<?php

declare(strict_types=1);

namespace Tests\Support;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\File;

/**
 * Helpers for design-system conformance tests (DS-01..DS-07).
 * Adjust the constants below if your paths differ.
 */
final class DesignSystem
{
    public const TOKENS = 'resources/css/tokens.css';

    public const PUBLIC_FONTS = 'public/fonts';

    /** @return list<array{rel:string,content:string}> */
    public static function files(array $dirs, array $suffixes): array
    {
        $out = [];
        foreach ($dirs as $dir) {
            $abs = base_path($dir);
            if (! is_dir($abs)) {
                continue;
            }
            foreach (File::allFiles($abs) as $file) {
                foreach ($suffixes as $suffix) {
                    if (str_ends_with($file->getFilename(), $suffix)) {
                        $out[] = [
                            // Forward slashes on every OS, so the allow-lists and TOKENS compare equal on Windows too.
                            'rel' => ltrim(str_replace('\\', '/', str_replace(base_path(), '', $file->getPathname())), '/'),
                            'content' => $file->getContents(),
                        ];
                        break;
                    }
                }
            }
        }

        return $out;
    }

    /** @return list<array{rel:string,content:string}> */
    public static function views(): array
    {
        return self::files(['resources/views'], ['.blade.php']);
    }

    /** CSS files except tokens.css (the only place raw colours may live). */
    public static function cssExceptTokens(): array
    {
        return array_values(array_filter(
            self::files(['resources/css'], ['.css']),
            fn (array $f) => $f['rel'] !== self::TOKENS,
        ));
    }

    public static function scripts(): array
    {
        return self::files(['resources/js'], ['.js', '.ts']);
    }

    /** @return array<string,string> custom property => value (as written) */
    public static function tokens(): array
    {
        $css = (string) file_get_contents(base_path(self::TOKENS));
        $css = preg_replace('#/\*.*?\*/#s', '', $css) ?? $css;
        preg_match_all('/(--[a-z0-9-]+)\s*:\s*([^;{}]+);/i', $css, $m, PREG_SET_ORDER);
        $out = [];
        foreach ($m as $match) {
            $out[$match[1]] = trim($match[2]);
        }

        return $out;
    }

    public static function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split($hex, 2));
        $f = fn (float $c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $f($r) + 0.7152 * $f($g) + 0.0722 * $f($b);
    }

    public static function contrast(string $fg, string $bg): float
    {
        $a = self::luminance($fg);
        $b = self::luminance($bg);
        [$hi, $lo] = $a >= $b ? [$a, $b] : [$b, $a];

        return ($hi + 0.05) / ($lo + 0.05);
    }

    public static function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }
}
