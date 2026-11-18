<?php

declare(strict_types=1);

namespace App\Domain\Guest\Exceptions;

use DomainException;

final class GuestLimitReached extends DomainException
{
    public static function for(int $limit, int $current, int $adding): self
    {
        return new self(sprintf('Batas tamu paket ini adalah %d (sekarang %d, akan menambah %d).', $limit, $current, $adding));
    }
}
