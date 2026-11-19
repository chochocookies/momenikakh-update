<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Media\Data\MediaData;
use App\Domain\Media\Data\MusicData;

/** The images and audio of one invitation, already turned into plain data for the view-model. */
final readonly class InvitationMedia
{
    /**
     * @param  list<MediaData>  $gallery
     * @param  array<int,MediaData>  $photos  person id => photo
     * @param  array<int,MediaData>  $qr  gift id => QR image
     */
    public function __construct(
        public ?MediaData $cover = null,
        public array $gallery = [],
        public array $photos = [],
        public array $qr = [],
        public ?MusicData $music = null,
    ) {}
}
