<?php

declare(strict_types=1);

namespace App\Domain\Media\Data;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * What a template needs for one image. Only conversions that have actually been generated are referenced,
 * so a page rendered while the queue is still working falls back to the clean original instead of a 404.
 */
final readonly class MediaData
{
    public function __construct(
        public string $url,
        public string $srcset,
        public ?string $thumb,
        public int $width,
        public int $height,
        public string $alt,
    ) {}

    public static function fromMedia(Media $m): self
    {
        $set = [];
        foreach (['sm' => 480, 'md' => 960, 'lg' => 1600] as $name => $w) {
            if ($m->hasGeneratedConversion($name)) {
                $set[] = $m->getUrl($name).' '.$w.'w';
            }
        }

        return new self(
            $m->hasGeneratedConversion('md') ? $m->getUrl('md') : $m->getUrl(),
            implode(', ', $set),
            $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : null,
            (int) $m->getCustomProperty('width', 0),
            (int) $m->getCustomProperty('height', 0),
            (string) $m->getCustomProperty('alt', ''),
        );
    }
}
