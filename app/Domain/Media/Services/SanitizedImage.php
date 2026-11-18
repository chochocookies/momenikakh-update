<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

final readonly class SanitizedImage
{
    public function __construct(
        public string $path,
        public string $mime,
        public string $extension,
        public int $width,
        public int $height,
        public int $bytes,
    ) {}
}
