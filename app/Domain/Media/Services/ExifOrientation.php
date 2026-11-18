<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

/** Reads the EXIF Orientation tag (1..8) from JPEG bytes without needing ext-exif. 1 = upright / unknown. */
final class ExifOrientation
{
    public static function read(string $jpeg): int
    {
        if (substr($jpeg, 0, 2) !== "\xFF\xD8") {
            return 1;
        }
        $pos = 2;
        $len = strlen($jpeg);
        while ($pos + 4 <= $len) {
            if ($jpeg[$pos] !== "\xFF") {
                break;
            }
            $marker = ord($jpeg[$pos + 1]);
            if ($marker === 0xFF) {
                $pos++;

                continue;
            }
            if ($marker === 0xD9 || $marker === 0xDA) { // end of image / start of scan
                break;
            }
            $size = unpack('n', substr($jpeg, $pos + 2, 2))[1] ?? 0;
            if ($size < 2) {
                break;
            }
            if ($marker === 0xE1 && substr($jpeg, $pos + 4, 6) === "Exif\0\0") {
                return self::fromTiff(substr($jpeg, $pos + 10, $size - 8));
            }
            $pos += 2 + $size;
        }

        return 1;
    }

    private static function fromTiff(string $tiff): int
    {
        $len = strlen($tiff);
        if ($len < 8) {
            return 1;
        }
        $order = substr($tiff, 0, 2);
        if ($order !== 'II' && $order !== 'MM') {
            return 1;
        }
        $little = $order === 'II';
        $u16 = static fn (int $o): int => $o + 2 <= $len ? (int) unpack($little ? 'v' : 'n', substr($tiff, $o, 2))[1] : 0;
        $u32 = static fn (int $o): int => $o + 4 <= $len ? (int) unpack($little ? 'V' : 'N', substr($tiff, $o, 4))[1] : 0;

        $ifd = $u32(4);
        if ($ifd < 8 || $ifd + 2 > $len) {
            return 1;
        }
        $count = $u16($ifd);
        for ($i = 0; $i < $count; $i++) {
            $entry = $ifd + 2 + $i * 12;
            if ($entry + 12 > $len) {
                break;
            }
            if ($u16($entry) === 0x0112) {
                $value = $u16($entry + 8); // SHORT, stored in the first two bytes of the value field

                return $value >= 1 && $value <= 8 ? $value : 1;
            }
        }

        return 1;
    }
}
