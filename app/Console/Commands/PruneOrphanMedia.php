<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Media\Services\OrphanPruner;
use Illuminate\Console\Command;

final class PruneOrphanMedia extends Command
{
    protected $signature = 'media:prune-orphans {--dry-run : Only report, delete nothing} {--grace= : Grace period in hours (default from config)}';

    protected $description = 'Delete media rows without an owner and storage folders without a media row (after a grace period)';

    public function handle(): int
    {
        $grace = $this->option('grace') !== null ? (int) $this->option('grace') : (int) config('media-limits.orphans.grace_hours', 24);
        $pruner = new OrphanPruner((string) config('media-limits.disk'), $grace);
        $report = $pruner->run((bool) $this->option('dry-run'));

        $verb = $report->dryRun ? 'Would delete' : 'Deleted';
        $this->info(sprintf('%s %d orphan media row(s) and %d orphan folder(s) older than %dh.', $verb, count($report->orphanRowIds), count($report->orphanDirectories), $grace));
        foreach ($report->unverifiable as $type) {
            $this->warn("Skipped unknown owner type (left untouched): {$type}");
        }

        return self::SUCCESS;
    }
}
