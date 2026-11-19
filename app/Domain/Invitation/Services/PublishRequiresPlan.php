<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use DomainException;

/** The invitation is a free sample: publishing needs the "Paket Template" (DEC-01). The dashboard turns this into the pay prompt. */
final class PublishRequiresPlan extends DomainException
{
    public static function forSample(): self
    {
        return new self('This invitation is a free sample. Publishing needs the Paket Template.');
    }
}
