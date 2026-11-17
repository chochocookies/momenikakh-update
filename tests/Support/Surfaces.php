<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Resolves the base URL of each surface (marketing, app, public).
 * Edit DEFAULTS if your local domains differ from config/surfaces.php.
 */
final class Surfaces
{
    private const DEFAULTS = [
        'marketing' => 'momenikah.test',
        'app' => 'app.momenikah.test',
        'public' => 'undangan.momenikah.test',
    ];

    public static function url(string $surface, string $path = '/'): string
    {
        $domain = config("surfaces.{$surface}.domain")
            ?? config("surfaces.domains.{$surface}")
            ?? self::DEFAULTS[$surface];

        return 'http://'.$domain.'/'.ltrim($path, '/');
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::DEFAULTS);
    }
}
