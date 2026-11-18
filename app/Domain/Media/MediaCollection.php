<?php

declare(strict_types=1);

namespace App\Domain\Media;

enum MediaCollection: string
{
    case Cover = 'cover';       // Invitation, single
    case Gallery = 'gallery';   // Invitation, many (limit from Entitlements max_photos)
    case Music = 'music';       // Invitation, single audio upload
    case Photo = 'photo';       // InvitationPerson, single
    case Qr = 'qr';             // InvitationGift, single
    case Audio = 'audio';       // MusicTrack (licensed catalog), single

    public function isImage(): bool
    {
        return ! in_array($this, [self::Music, self::Audio], true);
    }

    public function isSingle(): bool
    {
        return $this !== self::Gallery;
    }
}
