<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\Enums\InvitationStatus;
use DomainException;

final class InvalidTransition extends DomainException
{
    public static function between(InvitationStatus $from, InvitationStatus $to): self
    {
        return new self("Invitation cannot move from [{$from->value}] to [{$to->value}].");
    }
}
