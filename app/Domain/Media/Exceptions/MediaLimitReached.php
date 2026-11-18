<?php

declare(strict_types=1);

namespace App\Domain\Media\Exceptions;

use DomainException;

final class MediaLimitReached extends DomainException
{
    public static function for(string $collection, int $limit): self
    {
        return new self(sprintf('Batas %d berkas untuk "%s" sudah tercapai.', $limit, $collection));
    }
}
