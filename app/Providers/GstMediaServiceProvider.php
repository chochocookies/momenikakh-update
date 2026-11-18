<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Guest\Services\GuestCsvParser;
use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Guest\Services\PhoneNormalizer;
use App\Domain\Guest\Services\ShareMessageBuilder;
use App\Domain\Media\Services\AudioInspector;
use App\Domain\Media\Services\ImageInspector;
use App\Domain\Media\Services\ImageSanitizer;
use Illuminate\Support\ServiceProvider;

/** Register in bootstrap/providers.php (after DomainServiceProvider). */
final class GstMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PhoneNormalizer::class);
        $this->app->singleton(GuestCsvParser::class, fn ($app) => new GuestCsvParser(
            $app->make(PhoneNormalizer::class),
            (int) config('guests.import.max_rows'),
            (int) config('guests.import.max_bytes'),
            (int) config('guests.name_max'),
            (int) config('guests.group_max'),
        ));
        $this->app->singleton(GuestLinkBuilder::class, fn () => new GuestLinkBuilder(
            (string) (config('guests.public_base_url') ?: config('surfaces.public.url') ?: config('app.url')),
        ));
        $this->app->singleton(ShareMessageBuilder::class, fn () => new ShareMessageBuilder(
            (string) config('guests.share.default_message'),
            (int) config('guests.share.message_max'),
        ));

        $this->app->singleton(ImageInspector::class, fn () => new ImageInspector((array) config('media-limits.image'), (array) config('media-limits.profiles')));
        $this->app->singleton(ImageSanitizer::class, fn () => new ImageSanitizer((array) config('media-limits.image'), (array) config('media-limits.profiles')));
        $this->app->singleton(AudioInspector::class, fn () => new AudioInspector((array) config('media-limits.audio')));
    }
}
