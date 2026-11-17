<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Enums;

enum InvitationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Expired = 'expired';
    case Archived = 'archived';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Published, self::Archived],
            self::Published => [self::Draft, self::Expired, self::Archived],
            self::Expired => [self::Published, self::Archived],
            self::Archived => [self::Draft],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }
}
