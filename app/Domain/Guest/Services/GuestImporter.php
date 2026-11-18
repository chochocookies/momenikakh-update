<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Guest\Data\ImportReport;
use App\Domain\Invitation\Models\Invitation;
use DomainException;
use Illuminate\Support\Facades\DB;

/** GST-01: preview (no writes) then commit. Commit re-checks the guest limit inside a locked transaction. */
final class GuestImporter
{
    public function __construct(private readonly GuestCsvParser $parser, private readonly GuestService $guests) {}

    public function preview(Invitation $invitation, string $path): ImportReport
    {
        $report = $this->parser->parse($path);
        if ($report->fatal !== null) {
            return $report;
        }

        $existing = $invitation->guests()->get(['name', 'phone'])
            ->mapWithKeys(fn ($g) => [mb_strtolower($g->name).'|'.($g->phone ?? '') => true]);

        $fresh = [];
        foreach ($report->valid as $row) {
            if ($existing->has(mb_strtolower($row['name']).'|'.($row['phone'] ?? ''))) {
                $report->duplicates[] = $row['line'];
            } else {
                $fresh[] = $row;
            }
        }
        $report->valid = $fresh;
        sort($report->duplicates);

        return $report;
    }

    public function commit(Invitation $invitation, ImportReport $report): int
    {
        if (! $report->canCommit()) {
            throw new DomainException('Tidak ada baris valid untuk diimpor.');
        }

        return DB::transaction(function () use ($invitation, $report): int {
            Invitation::query()->whereKey($invitation->id)->lockForUpdate()->first(); // serialise concurrent imports
            $this->guests->assertCapacity($invitation, count($report->valid));

            foreach ($report->valid as $row) {
                $invitation->guests()->create(['name' => $row['name'], 'group' => $row['group'], 'phone' => $row['phone']]);
            }

            return count($report->valid);
        });
    }
}
