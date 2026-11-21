<?php

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Persists VALIDATED data for one editor step. Controllers/Livewire pass `$request->validated()`.
 * Child rows are synced by id (update existing, create new, delete missing) so ids stay stable
 * for later media attachments.
 */
final class InvitationWriter
{
    private const PEOPLE = ['role', 'name', 'nickname', 'parents_text', 'bio', 'birth_date', 'instagram'];

    private const EVENTS = ['kind', 'label', 'starts_at', 'ends_at', 'timezone', 'venue_name', 'address', 'maps_url', 'latitude', 'longitude', 'dress_code', 'notes'];

    private const STORY = ['title', 'body', 'happened_on'];

    private const GIFTS = ['type', 'provider', 'holder_name', 'number', 'address_text', 'is_active'];

    private const SETTINGS = ['palette', 'motion', 'rsvp_enabled', 'wishes_enabled', 'gifts_enabled'];

    public function __construct(private readonly EventTypeRegistry $registry) {}

    /** @param array<string,mixed> $data validated data for the step */
    public function apply(Invitation $invitation, string $step, array $data): Invitation
    {
        if (! in_array($step, EventTypeDefinition::STEPS, true)) {
            throw new InvalidArgumentException("Unknown editor step [{$step}].");
        }

        return DB::transaction(function () use ($invitation, $step, $data) {
            match ($step) {
                'basics' => $this->writeBasics($invitation, $data),
                'people' => $this->sync($invitation->people(), $data['people'] ?? [], self::PEOPLE),
                'events' => $this->syncEvents($invitation, $data['events'] ?? []),
                'story' => $this->sync($invitation->storyItems(), $data['story'] ?? [], self::STORY),
                'gifts' => $this->sync($invitation->gifts(), $data['gifts'] ?? [], self::GIFTS, ['is_active' => true]),
                'content' => $this->writeContent($invitation, $data['content'] ?? []),
                'settings' => $this->writeSettings($invitation, $data['settings'] ?? []),
            };

            return $invitation->refresh();
        });
    }

    /** @param array<string,array<string,mixed>> $payloadByStep */
    public function applyAll(Invitation $invitation, array $payloadByStep): Invitation
    {
        foreach (EventTypeDefinition::STEPS as $step) {
            if (isset($payloadByStep[$step])) {
                $this->apply($invitation, $step, $payloadByStep[$step]);
            }
        }

        return $invitation->refresh();
    }

    /** @param list<array<string,mixed>> $rows */
    private function syncEvents(Invitation $invitation, array $rows): void
    {
        $tz = $invitation->timezone;
        $rows = array_map(function (array $row) use ($tz): array {
            $zone = ! empty($row['timezone']) ? $row['timezone'] : $tz;
            $row['timezone'] = ! empty($row['timezone']) ? $row['timezone'] : null;
            foreach (['starts_at', 'ends_at'] as $key) {
                if (! empty($row[$key])) {
                    // Editor sends local wall-clock time of the event's zone; the database stores UTC.
                    $row[$key] = CarbonImmutable::createFromFormat('Y-m-d H:i', $row[$key], $zone)->utc();
                }
            }

            return $row;
        }, $rows);
        $this->sync($invitation->events(), $rows, self::EVENTS);
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  list<string>  $columns
     * @param  array<string,mixed>  $defaults
     */
    private function sync(HasMany $relation, array $rows, array $columns, array $defaults = []): void
    {
        $existing = $relation->get()->keyBy('id');
        $keep = [];

        foreach (array_values($rows) as $i => $row) {
            $attrs = $defaults + ['sort' => $i];
            foreach ($columns as $col) {
                $attrs[$col] = array_key_exists($col, $row) ? $row[$col] : ($defaults[$col] ?? null);
            }
            $id = $row['id'] ?? null;
            if ($id !== null && $existing->has((int) $id)) {
                $existing[(int) $id]->update($attrs);
                $keep[] = (int) $id;
            } else {
                $keep[] = $relation->create($attrs)->getKey();
            }
        }

        $relation->whereNotIn('id', $keep)->delete();
    }

    /** @param array<string,mixed> $content */
    private function writeContent(Invitation $invitation, array $content): void
    {
        $allowed = $this->registry->for($invitation->event_type)->contentKeys();
        $invitation->content = array_filter(
            Arr::only($content, $allowed),
            fn ($v) => $v !== null && $v !== '',
        );
        $invitation->save();
    }

    /** @param array<string,mixed> $settings */
    private function writeSettings(Invitation $invitation, array $settings): void
    {
        $invitation->settings = array_replace($invitation->settings ?? [], Arr::only($settings, self::SETTINGS));
        $invitation->save();
    }

    /**
     * Handle the basics step including slug changes with alias creation (PUB-05).
     */
    private function writeBasics(Invitation $invitation, array $data): void
    {
        $oldSlug = $invitation->slug;
        $newSlug = $data['slug'] ?? $oldSlug;

        $invitation->fill(Arr::only($data, ['title', 'slug', 'template_slug', 'locale', 'timezone']))->save();

        // Create slug alias if slug changed (PUB-05)
        if ($newSlug !== $oldSlug) {
            $invitation->createSlugAlias($oldSlug);
        }

        // Invalidate cache for both old and new slugs (PUB-03)
        $this->invalidateInvitationCache($oldSlug);
        if ($newSlug !== $oldSlug) {
            $this->invalidateInvitationCache($newSlug);
        }
    }

    /**
     * Invalidate the invitation cache (PUB-03).
     */
    private function invalidateInvitationCache(string $slug): void
    {
        Cache::store('redis')->forget("invitation:{$slug}");
    }
}
