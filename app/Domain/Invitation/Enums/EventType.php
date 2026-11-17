<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Enums;

/** The six supported event types. Add a case + a config/event-types.php entry to add one. */
enum EventType: string
{
    case Wedding = 'wedding';
    case WalimatulUrs = 'walimatul_urs';
    case Engagement = 'engagement';
    case Aqiqah = 'aqiqah';
    case Khitanan = 'khitanan';
    case Birthday = 'birthday';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
