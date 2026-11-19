<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

use App\Domain\Media\Exceptions\SanitizeException;
use GdImage;

/**
 * MED-02: re-encodes an upload into a clean image BEFORE it is stored.
 *  - drops ALL metadata (EXIF incl. GPS location, XMP, comments, ICC) because the file is decoded and re-written;
 *  - applies the EXIF orientation first, so phone photos are not stored sideways;
 *  - caps the longest edge (no upscaling);
 *  - neutralises polyglot files (a JPEG with code appended): only pixels survive.
 * The stored "original" is therefore safe to serve; conversions (480/960/1600, thumb, og) derive from it.
 */
final class ImageSanitizer
{
    /**
     * @param  array<string,mixed>  $image  config('media-limits.image')
     * @param  array<string,array<string,mixed>>  $profiles  config('media-limits.profiles')
     */
    public function __construct(private readonly array $image, private readonly array $profiles) {}

    /** @param string $destBase destination path WITHOUT extension */
    public function sanitize(string $source, string $profile, string $destBase): SanitizedImage
    {
        $bytes = @file_get_contents($source);
        if ($bytes === false || $bytes === '') {
            throw new SanitizeException('Berkas tidak dapat dibaca.');
        }
        $info = @getimagesizefromstring($bytes);
        if ($info === false) {
            throw new SanitizeException('Gambar rusak atau tidak dapat dibaca.');
        }
        $mime = $info['mime'];

        $im = @imagecreatefromstring($bytes);
        if (! $im instanceof GdImage) {
            throw new SanitizeException('Gambar rusak atau tidak dapat didekode.');
        }

        if ($mime === 'image/jpeg') {
            $im = $this->orient($im, ExifOrientation::read($bytes));
        }
        unset($bytes);

        imagepalettetotruecolor($im);
        $im = $this->constrain($im, (int) $this->image['sanitized_max_edge']);

        $output = (string) ($this->profiles[$profile]['output'] ?? 'webp');
        $path = $destBase.'.'.$output;
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $ok = $output === 'png'
            ? imagepng($im, $path, 9)
            : imagewebp($im, $path, (int) $this->image['quality']);
        $w = imagesx($im);
        $h = imagesy($im);
        imagedestroy($im);

        if (! $ok || ! is_file($path)) {
            throw new SanitizeException('Gagal menyimpan gambar.');
        }

        return new SanitizedImage($path, $output === 'png' ? 'image/png' : 'image/webp', $output, $w, $h, (int) filesize($path));
    }

    private function orient(GdImage $im, int $orientation): GdImage
    {
        switch ($orientation) {
            case 2:
                imageflip($im, IMG_FLIP_HORIZONTAL);

                return $im;
            case 3:
                return $this->rotate($im, 180);
            case 4:
                imageflip($im, IMG_FLIP_VERTICAL);

                return $im;
            case 5: // transpose = rotate 90 CW, then mirror horizontally
                $im = $this->rotate($im, 270);
                imageflip($im, IMG_FLIP_HORIZONTAL);

                return $im;
            case 6: // rotate 90 CW (GD rotates counter-clockwise, hence 270)
                return $this->rotate($im, 270);
            case 7: // transverse = rotate 90 CW, then mirror vertically
                $im = $this->rotate($im, 270);
                imageflip($im, IMG_FLIP_VERTICAL);

                return $im;
            case 8:
                return $this->rotate($im, 90);
            default:
                return $im;
        }
    }

    private function rotate(GdImage $im, int $ccwDegrees): GdImage
    {
        $rotated = imagerotate($im, $ccwDegrees, 0);
        if (! $rotated instanceof GdImage) {
            throw new SanitizeException('Gagal memutar gambar.');
        }
        imagedestroy($im);

        return $rotated;
    }

    private function constrain(GdImage $im, int $maxEdge): GdImage
    {
        $w = imagesx($im);
        $h = imagesy($im);
        $longest = max($w, $h);
        if ($longest <= $maxEdge) {
            return $im; // never upscale
        }
        $scale = $maxEdge / $longest;
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $scaled = imagescale($im, max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)), IMG_BICUBIC);
        if (! $scaled instanceof GdImage) {
            throw new SanitizeException('Gagal mengecilkan gambar.');
        }
        imagedestroy($im);

        return $scaled;
    }
}
