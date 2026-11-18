<?php

declare(strict_types=1);

namespace App\Domain\Media\Models;

use Database\Factories\MusicTrackFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * MED-05 (optional, behind config media-limits.audio.catalog_enabled until DEC-06):
 * a licensed track that users may pick instead of uploading their own file.
 * A track is selectable ONLY with a named license AND a recorded verification, so nothing unlicensed ships by accident.
 */
class MusicTrack extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'music_tracks';

    protected $fillable = ['title', 'artist', 'license', 'license_url', 'requires_attribution', 'attribution_text', 'is_active', 'verified_at', 'sort'];

    protected static function newFactory(): MusicTrackFactory
    {
        return MusicTrackFactory::new();
    }

    protected function casts(): array
    {
        return ['requires_attribution' => 'boolean', 'is_active' => 'boolean', 'verified_at' => 'datetime'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')->singleFile()->acceptsMimeTypes(['audio/mpeg', 'audio/ogg', 'application/ogg']);
    }

    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('verified_at')
            ->whereNotNull('license')->where('license', '!=', '');
    }
}
