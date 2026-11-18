<?php

declare(strict_types=1);

namespace App\Domain\Media\Concerns;

/**
 * Add to App\Domain\Invitation\Models\InvitationGift (implements HasMedia, use InteractsWithMedia) and forward
 * registerMediaCollections() to registerGiftMediaCollections().
 * No conversions on purpose: the QR code is stored once as a clean lossless PNG. Re-encoding a transparent
 * PNG through conversions has been reported to produce black backgrounds, and a QR must stay pixel-exact.
 */
trait RegistersGiftMedia
{
    public function registerGiftMediaCollections(): void
    {
        $this->addMediaCollection('qr')->singleFile()->acceptsMimeTypes(['image/png']);
    }
}
