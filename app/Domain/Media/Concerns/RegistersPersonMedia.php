<?php

declare(strict_types=1);

namespace App\Domain\Media\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Add to App\Domain\Invitation\Models\InvitationPerson (implements HasMedia, use InteractsWithMedia) and forward:
 *   registerMediaCollections()  -> $this->registerPersonMediaCollections();
 *   registerMediaConversions()  -> $this->registerPersonMediaConversions($media);
 */
trait RegistersPersonMedia
{
    public function registerPersonMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile()->acceptsMimeTypes(['image/webp', 'image/png', 'image/jpeg']);
    }

    public function registerPersonMediaConversions(?Media $media = null): void
    {
        foreach (['sm' => 480, 'md' => 960] as $name => $width) {
            $this->addMediaConversion($name)->performOnCollections('photo')->fit(Fit::Max, $width, $width * 4)->format('webp')->quality(80);
        }
        $this->addMediaConversion('thumb')->performOnCollections('photo')->fit(Fit::Crop, 320, 320)->format('webp')->quality(75);
    }
}
