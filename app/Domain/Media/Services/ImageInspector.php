<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

/**
 * MED-03: checks an uploaded image WITHOUT decoding it (so a decompression bomb cannot exhaust memory).
 * Trusts the file's real content (finfo + image header), never the client-supplied name or MIME type.
 */
final class ImageInspector
{
    /**
     * @param  array<string,mixed>  $image  config('media-limits.image')
     * @param  array<string,array<string,mixed>>  $profiles  config('media-limits.profiles')
     */
    public function __construct(private readonly array $image, private readonly array $profiles) {}

    public function inspect(string $path, string $profile): InspectionResult
    {
        if (! is_file($path) || ! is_readable($path)) {
            return new InspectionResult(false, ['unreadable' => 'Berkas tidak dapat dibaca.']);
        }
        $bytes = (int) filesize($path);
        if ($bytes === 0) {
            return new InspectionResult(false, ['empty' => 'Berkas kosong.'], bytes: 0);
        }

        $errors = [];
        if ($bytes > $this->image['max_bytes']) {
            $errors['too_large'] = sprintf('Ukuran berkas terlalu besar (maksimal %s).', $this->humanBytes($this->image['max_bytes']));
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $path) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if (! in_array($mime, $this->image['allowed_mimes'], true)) {
            $errors['bad_mime'] = match (true) {
                $mime === 'image/svg+xml' || $this->looksLikeSvg($path) => 'Format SVG tidak didukung. Gunakan JPEG, PNG, atau WebP.',
                in_array($mime, ['image/heic', 'image/heif'], true) => 'Format HEIC belum didukung. Pilih foto sebagai JPEG, PNG, atau WebP.',
                default => 'Format berkas tidak didukung. Gunakan JPEG, PNG, atau WebP.',
            };

            return new InspectionResult(false, $errors, $mime, bytes: $bytes);
        }

        $size = @getimagesize($path); // reads the header only
        if ($size === false) {
            $errors['unreadable'] = 'Gambar rusak atau tidak dapat dibaca.';

            return new InspectionResult(false, $errors, $mime, bytes: $bytes);
        }
        [$w, $h] = [(int) $size[0], (int) $size[1]];
        if (($size['mime'] ?? '') !== $mime) {
            $errors['mime_mismatch'] = 'Isi berkas tidak sesuai dengan formatnya.';
        }
        if ($w < 1 || $h < 1) {
            $errors['unreadable'] = 'Gambar rusak atau tidak dapat dibaca.';
        }
        if ($w > $this->image['max_width'] || $h > $this->image['max_height'] || $w * $h > $this->image['max_pixels']) {
            $errors['too_many_pixels'] = sprintf('Resolusi gambar terlalu besar (maksimal %s megapiksel).', number_format($this->image['max_pixels'] / 1_000_000, 0));
        }
        $min = $this->profiles[$profile] ?? [];
        if (isset($min['min_width']) && ($w < $min['min_width'] || $h < $min['min_height'])) {
            $errors['too_small'] = sprintf('Gambar terlalu kecil (minimal %d x %d piksel).', $min['min_width'], $min['min_height']);
        }

        return new InspectionResult($errors === [], $errors, $mime, $w, $h, $bytes);
    }

    private function looksLikeSvg(string $path): bool
    {
        $head = (string) file_get_contents($path, false, null, 0, 1024);

        return stripos($head, '<svg') !== false;
    }

    private function humanBytes(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
