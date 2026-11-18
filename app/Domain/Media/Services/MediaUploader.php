<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

use App\Domain\Media\Exceptions\InvalidMediaException;
use App\Domain\Media\Exceptions\MediaLimitReached;
use App\Domain\Media\MediaCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Single entry point for uploads. Inspect -> sanitize (re-encode, strip EXIF) -> store under a random name.
 * The client's file name, MIME type and metadata are never stored.
 */
final class MediaUploader
{
    public function __construct(
        private readonly ImageInspector $images,
        private readonly ImageSanitizer $sanitizer,
        private readonly AudioInspector $audio,
    ) {}

    /** @param array{alt?:?string} $meta */
    public function attachImage(HasMedia $model, MediaCollection $collection, UploadedFile|string $file, array $meta = [], ?int $maxFiles = null): Media
    {
        $path = $this->path($file);
        $result = $this->images->inspect($path, $collection->value);
        if (! $result->ok) {
            throw InvalidMediaException::fromInspection($result);
        }
        if ($maxFiles !== null && $model->media()->where('collection_name', $collection->value)->count() >= $maxFiles) {
            throw MediaLimitReached::for($collection->value, $maxFiles);
        }

        $base = sys_get_temp_dir().'/mn-'.Str::uuid();
        $clean = $this->sanitizer->sanitize($path, $collection->value, $base);
        try {
            return $model->addMedia($clean->path)
                ->preservingOriginal()
                ->usingFileName(Str::uuid().'.'.$clean->extension)
                ->usingName($collection->value)
                ->withCustomProperties([
                    'width' => $clean->width,
                    'height' => $clean->height,
                    'alt' => isset($meta['alt']) ? mb_substr(trim((string) $meta['alt']), 0, 150) : '',
                ])
                ->toMediaCollection($collection->value, (string) config('media-limits.disk'));
        } finally {
            @unlink($clean->path);
        }
    }

    public function attachAudio(HasMedia $model, MediaCollection $collection, UploadedFile|string $file): Media
    {
        $path = $this->path($file);
        $clientName = $file instanceof UploadedFile ? $file->getClientOriginalName() : null;
        $result = $this->audio->inspect($path, $clientName);
        if (! $result->ok) {
            throw InvalidMediaException::fromInspection($result);
        }
        $extension = $this->audio->signature($path); // 'mp3' | 'ogg', from the content, not the client name

        return $model->addMedia($path)
            ->preservingOriginal()
            ->usingFileName(Str::uuid().'.'.$extension)
            ->usingName($collection->value)
            ->toMediaCollection($collection->value, (string) config('media-limits.disk'));
    }

    private function path(UploadedFile|string $file): string
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new InvalidMediaException('Berkas tidak dapat dibaca.', ['unreadable' => 'Berkas tidak dapat dibaca.']);
        }

        return $path;
    }
}
