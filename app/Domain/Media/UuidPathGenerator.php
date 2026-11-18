<?php

declare(strict_types=1);

namespace App\Domain\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * MED-01: storage layout "ab/ab12…-uuid/…". The directory is the media UUID (random), never the numeric id,
 * so URLs of draft invitations cannot be guessed by counting. The two-character shard keeps directories small
 * and lets OrphanPruner list the bucket in manageable slices.
 */
final class UuidPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->base($media);
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->base($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->base($media).'responsive-images/';
    }

    private function base(Media $media): string
    {
        return substr($media->uuid, 0, 2).'/'.$media->uuid.'/';
    }
}
