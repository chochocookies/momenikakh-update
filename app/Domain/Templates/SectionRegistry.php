<?php

declare(strict_types=1);

namespace App\Domain\Templates;

/**
 * The section keys a template may render. Each key has a partial in resources/templates/_shared/sections.
 * config/event-types.php still says "gifts" and "wishes"; the partials are named "gift" and "guestbook".
 */
final class SectionRegistry
{
    /** @var list<string> */
    public const KEYS = ['cover', 'opening', 'people', 'events', 'story', 'gallery', 'gift', 'rsvp', 'guestbook', 'countdown', 'map', 'music', 'closing'];

    /** @var array<string,string> older or event-type section name => section key (config/event-types.php, TEMPLATE_SPEC) */
    public const ALIASES = ['gifts' => 'gift', 'wishes' => 'guestbook', 'couple' => 'people', 'person' => 'people'];

    public static function normalize(string $key): string
    {
        return self::ALIASES[$key] ?? $key;
    }

    public static function isValid(string $key): bool
    {
        return in_array($key, self::KEYS, true);
    }
}
