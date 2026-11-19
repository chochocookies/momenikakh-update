<?php

declare(strict_types=1);

namespace App\Domain\Media\Services;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * MED-04: removes media that nothing uses any more, and ONLY that.
 *  1) media rows whose owner model no longer exists (soft-deleted owners still count as existing);
 *  2) storage directories ("ab/uuid") that have no media row (failed or interrupted uploads, leftovers);
 * Both only after a grace period, so an upload in progress is never touched. Model types that cannot be
 * resolved are skipped and reported, never deleted. Run with $dryRun first.
 */
final class OrphanPruner
{
    public function __construct(private readonly string $disk, private readonly int $graceHours = 24) {}

    public function run(bool $dryRun = true, ?CarbonInterface $now = null): PruneReport
    {
        $cutoff = ($now ?? now())->copy()->subHours($this->graceHours);
        $unverifiable = [];
        $rowIds = $this->orphanRows($cutoff, $unverifiable);
        $dirs = $this->orphanDirectories($cutoff);

        if (! $dryRun) {
            foreach (array_chunk($rowIds, 200) as $chunk) {
                Media::query()->whereIn('id', $chunk)->get()->each->delete(); // the Media observer removes the files too
            }
            foreach ($dirs as $dir) {
                Storage::disk($this->disk)->deleteDirectory($dir);
            }
        }

        return new PruneReport($rowIds, $dirs, array_values(array_unique($unverifiable)), $dryRun);
    }

    /** @param list<string> $unverifiable @return list<int> */
    private function orphanRows(CarbonInterface $cutoff, array &$unverifiable): array
    {
        $orphans = [];
        Media::query()->where('created_at', '<', $cutoff)->select(['id', 'model_type', 'model_id'])
            ->chunkById(1000, function ($chunk) use (&$orphans, &$unverifiable): void {
                foreach ($chunk->groupBy('model_type') as $type => $items) {
                    if (! is_string($type) || ! class_exists($type) || ! is_subclass_of($type, Model::class)) {
                        $unverifiable[] = (string) $type;

                        continue;
                    }
                    /** @var Model $probe */
                    $probe = new $type;
                    $query = $type::query();
                    if (in_array(SoftDeletes::class, class_uses_recursive($type), true)) {
                        $query = $query->withoutGlobalScope(SoftDeletingScope::class);
                    }
                    $existing = array_flip($query->whereIn($probe->getKeyName(), $items->pluck('model_id')->unique()->all())
                        ->pluck($probe->getKeyName())->map(fn ($v) => (string) $v)->all());
                    foreach ($items as $media) {
                        if (! isset($existing[(string) $media->model_id])) {
                            $orphans[] = (int) $media->id;
                        }
                    }
                }
            });

        return $orphans;
    }

    /** @return list<string> */
    private function orphanDirectories(CarbonInterface $cutoff): array
    {
        $disk = Storage::disk($this->disk);
        $orphans = [];
        foreach ($disk->directories('') as $shard) {
            if (preg_match('/^[0-9a-f]{2}$/', $shard) !== 1) {
                continue; // not our layout (e.g. temporary uploads): never touch
            }
            $known = array_flip(Media::query()->where('uuid', 'like', $shard.'%')->pluck('uuid')->all());
            foreach ($disk->directories($shard) as $dir) {
                $uuid = basename($dir);
                if (! Str::isUuid($uuid) || isset($known[$uuid])) {
                    continue;
                }
                $files = $disk->allFiles($dir);
                $newest = $files === [] ? 0 : max(array_map(fn (string $f) => $disk->lastModified($f), $files));
                if ($newest <= $cutoff->getTimestamp()) {
                    $orphans[] = $dir;
                }
            }
        }

        return $orphans;
    }
}
