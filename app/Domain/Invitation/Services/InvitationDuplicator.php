<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;

/**
 * DSH-09: a copy of an invitation that stands on its own. A new slug, a draft, the same data, and its own copies of
 * the files (cover, gallery, music, person photos, gift QR codes), so deleting or changing one never touches the other.
 * The copy never carries over publishing, the plan, guests, RSVPs or wishes: those belong to the original event.
 */
final class InvitationDuplicator
{
    public function __construct(private readonly InvitationWriter $writer, private readonly SlugGenerator $slugs) {}

    public function duplicate(Invitation $source, User $owner): Invitation
    {
        return DB::transaction(function () use ($source, $owner): Invitation {
            $title = mb_substr('Salinan '.$source->title, 0, 120);
            $copy = $owner->invitations()->create([
                'event_type' => $source->event_type,
                'template_slug' => $source->template_slug,
                'slug' => $this->slugs->generate($title),
                'title' => $title,
                'status' => InvitationStatus::Draft,
                'locale' => $source->locale,
                'timezone' => $source->timezone,
            ]);

            $data = InvitationData::fromModel($source)->toArray();
            $this->writer->applyAll($copy, [
                'people' => ['people' => $this->withoutIds($data['people'])],
                'events' => ['events' => $this->eventsFor($source, $data['events'])],
                'story' => ['story' => $this->withoutIds($data['story'])],
                'gifts' => ['gifts' => $this->withoutIds($data['gifts'])],
                'content' => ['content' => $data['content']],
                'settings' => ['settings' => $data['settings']],
            ]);
            // Settings the writer does not own (the music choice, the share message) travel as they are.
            $copy->forceFill(['settings' => $source->settings ?? [], 'content' => $source->content ?? []])->save();

            $this->copyMedia($source, $copy, ['cover', 'gallery', 'music']);
            // People and gifts are new rows in the same order, so the n-th of the copy matches the n-th of the original.
            foreach ($source->people()->orderBy('sort')->get()->values() as $i => $person) {
                $this->copyMedia($person, $copy->people()->orderBy('sort')->get()->values()[$i], ['photo']);
            }
            foreach ($source->gifts()->orderBy('sort')->get()->values() as $i => $gift) {
                $this->copyMedia($gift, $copy->gifts()->orderBy('sort')->get()->values()[$i], ['qr']);
            }

            return $copy->refresh();
        });
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @return list<array<string,mixed>>
     */
    private function withoutIds(array $rows): array
    {
        return array_map(fn (array $row) => array_diff_key($row, ['id' => 1]), $rows);
    }

    /**
     * Events keep their own zone only when they had one (the editor shows an empty zone as "follows the invitation").
     *
     * @param  list<array<string,mixed>>  $rows
     * @return list<array<string,mixed>>
     */
    private function eventsFor(Invitation $source, array $rows): array
    {
        $own = $source->events()->pluck('timezone', 'id');

        return array_map(function (array $row) use ($own): array {
            $row['timezone'] = $own[$row['id']] ?? null;
            unset($row['id']);

            return $row;
        }, $rows);
    }

    /** @param  list<string>  $collections */
    private function copyMedia(HasMedia $from, HasMedia $to, array $collections): void
    {
        foreach ($collections as $collection) {
            foreach ($from->getMedia($collection) as $media) {
                $media->copy($to, $collection);
            }
        }
    }
}
