<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

/**
 * MED-05: validates an uploaded music file (mp3 or ogg only) from its real content: size, finfo MIME and
 * the container signature (ID3 tag or MPEG frame sync for mp3, "OggS" for ogg).
 */
final class AudioInspector
{
    /** @param array<string,mixed> $audio config('media-limits.audio') */
    public function __construct(private readonly array $audio) {}

    public function inspect(string $path, ?string $clientName = null): InspectionResult
    {
        if (! is_file($path) || ! is_readable($path)) {
            return new InspectionResult(false, ['unreadable' => 'Berkas tidak dapat dibaca.']);
        }
        $bytes = (int) filesize($path);
        if ($bytes === 0) {
            return new InspectionResult(false, ['empty' => 'Berkas kosong.']);
        }
        $errors = [];
        if ($bytes > $this->audio['max_bytes']) {
            $errors['too_large'] = sprintf('Ukuran berkas terlalu besar (maksimal %s MB).', round($this->audio['max_bytes'] / 1048576, 1));
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $path) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $kind = $this->signature($path);
        if (! in_array($mime, $this->audio['allowed_mimes'], true) || $kind === null) {
            $errors['bad_mime'] = 'Format musik tidak didukung. Gunakan MP3 atau OGG.';
        }
        if ($clientName !== null) {
            $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
            if (! in_array($ext, $this->audio['allowed_extensions'], true)) {
                $errors['bad_extension'] = 'Ekstensi berkas harus .mp3 atau .ogg.';
            } elseif ($kind !== null && $ext !== $kind) {
                $errors['extension_mismatch'] = 'Ekstensi berkas tidak sesuai dengan isinya.';
            }
        }

        return new InspectionResult($errors === [], $errors, $mime, bytes: $bytes);
    }

    /** @return 'mp3'|'ogg'|null */
    public function signature(string $path): ?string
    {
        $head = (string) file_get_contents($path, false, null, 0, 4);
        if (str_starts_with($head, 'OggS')) {
            return 'ogg';
        }
        if (str_starts_with($head, 'ID3')) {
            return 'mp3';
        }
        if (strlen($head) >= 2 && $head[0] === "\xFF" && (ord($head[1]) & 0xE0) === 0xE0) {
            return 'mp3';
        }

        return null;
    }
}
