<?php

declare(strict_types=1);

namespace App\Domain\Media\Exceptions;

use App\Domain\Media\Services\InspectionResult;
use DomainException;

final class InvalidMediaException extends DomainException
{
    /** @param array<string,string> $errors */
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }

    public static function fromInspection(InspectionResult $result): self
    {
        return new self($result->firstMessage() ?? 'Berkas tidak valid.', $result->errors);
    }
}
