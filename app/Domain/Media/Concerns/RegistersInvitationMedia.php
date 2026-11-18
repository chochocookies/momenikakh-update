<?php

declare(strict_types=1);

namespace App\Domain\Media\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Add to App\Domain\Invitation\Models\Invitation:
 *   implements \Spatie\MediaLibrary\HasMedia
 *   use \Spatie\MediaLibrary\InteractsWithMedia, \App\Domain\Media\Concerns\RegistersInvitationMedia;
 *   public function registerMediaCollections(): void { $this->registerInvitationMediaCollections(); }
 *   public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void { $this->registerInvitationMediaConversions($media); }
 * (Distinct method names avoid a trait collision with InteractsWithMedia.)
 */
trait RegistersInvitationMedia
{
    public function registerInvitationMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(['image/webp', 'image/png', 'image/jpeg']);
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/webp', 'image/png', 'image/jpeg']);
        $this->addMediaCollection('music')->singleFile()->acceptsMimeTypes(['audio/mpeg', 'audio/ogg', 'application/ogg']);
    }

    public function registerInvitationMediaConversions(?Media $media = null): void
    {
        // MED-02: three widths. Fit::Max only ever shrinks, so small originals are never upscaled.
        // performOnCollections() comes first: it is a Conversion method, the rest are forwarded to the image driver.
        foreach (['sm' => 480, 'md' => 960, 'lg' => 1600] as $name => $width) {
            $this->addMediaConversion($name)
                ->performOnCollections('cover', 'gallery')
                ->fit(Fit::Max, $width, $width * 4)
                ->format('webp')->quality(80);
        }
        $this->addMediaConversion('thumb')
            ->performOnCollections('cover', 'gallery')
            ->fit(Fit::Crop, 320, 320)
            ->format('webp')->quality(75);

        // Link-preview image (WhatsApp, Facebook, X): JPEG is the safest format for scrapers.
        $this->addMediaConversion('og')
            ->performOnCollections('cover')
            ->fit(Fit::Crop, 1200, 630)
            ->format('jpg')->quality(82);
    }
}
