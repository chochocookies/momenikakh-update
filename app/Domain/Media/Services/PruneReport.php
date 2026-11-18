<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

final readonly class PruneReport
{
    /**
     * @param  list<int>  $orphanRowIds  media rows whose owner no longer exists
     * @param  list<string>  $orphanDirectories  storage directories with no media row
     * @param  list<string>  $unverifiable  model types that could not be checked (left untouched)
     */
    public function __construct(
        public array $orphanRowIds,
        public array $orphanDirectories,
        public array $unverifiable,
        public bool $dryRun,
    ) {}
}
