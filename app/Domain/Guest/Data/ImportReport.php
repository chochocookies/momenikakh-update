<?php

declare(strict_types=1);

namespace App\Domain\Guest\Data;

/** Result of parsing a guest CSV. Nothing is written to the database by the parser. */
final class ImportReport
{
    /**
     * @param  list<array{line:int,name:string,group:?string,phone:?string}>  $valid  rows ready to import
     * @param  list<array{line:int,field:string,message:string}>  $errors  rejected rows
     * @param  list<int>  $duplicates  line numbers skipped as duplicates
     */
    public function __construct(
        public array $valid = [],
        public array $errors = [],
        public array $duplicates = [],
        public int $totalRows = 0,
        public ?string $fatal = null,
    ) {}

    public static function fatal(string $message): self
    {
        return new self(fatal: $message);
    }

    public function canCommit(): bool
    {
        return $this->fatal === null && $this->valid !== [];
    }

    public function hasProblems(): bool
    {
        return $this->fatal !== null || $this->errors !== [] || $this->duplicates !== [];
    }
}
