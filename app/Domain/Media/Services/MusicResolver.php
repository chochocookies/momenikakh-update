<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\Data\MusicData;
use App\Domain\Media\Models\MusicTrack;

/**
 * Which music plays on an invitation. settings.music = ['source' => 'upload'|'catalog', 'track_id' => int|null].
 * Returns null when there is none, so the page simply renders no player.
 */
final class MusicResolver
{
    public function for(Invitation $invitation): ?MusicData
    {
        $music = $invitation->settings['music'] ?? null;
        if (! is_array($music)) {
            return null;
        }

        if (($music['source'] ?? null) === 'upload') {
            $media = $invitation->getFirstMedia('music');

            return $media ? new MusicData($media->getUrl(), (string) ($music['title'] ?? 'Musik latar')) : null;
        }

        if (($music['source'] ?? null) === 'catalog' && config('media-limits.audio.catalog_enabled')) {
            $track = MusicTrack::query()->selectable()->find($music['track_id'] ?? 0);
            $media = $track?->getFirstMedia('audio');

            return $media
                ? new MusicData($media->getUrl(), $track->title, $track->requires_attribution ? $track->attribution_text : null)
                : null;
        }

        return null;
    }
}
