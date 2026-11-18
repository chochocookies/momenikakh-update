<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

final readonly class InspectionResult
{
    /** @param array<string,string> $errors code => human message (Indonesian) */
    public function __construct(
        public bool $ok,
        public array $errors = [],
        public ?string $mime = null,
        public ?int $width = null,
        public ?int $height = null,
        public int $bytes = 0,
    ) {}

    public function firstMessage(): ?string
    {
        return $this->errors === [] ? null : array_values($this->errors)[0];
    }

    public function has(string $code): bool
    {
        return isset($this->errors[$code]);
    }
}
