<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Data;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;

/**
 * The read-only view-model every template receives (the only data source for templates).
 * Built from the model, never from request input.
 */
final readonly class InvitationData
{
    /**
     * @param  list<PersonData>  $people
     * @param  list<EventData>  $events
     * @param  list<StoryItemData>  $story
     * @param  list<GiftData>  $gifts
     * @param  array<string,string>  $content
     * @param  array<string,mixed>  $settings
     * @param  list<string>  $sections
     */
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public EventType $eventType,
        public ?string $templateSlug,
        public string $locale,
        public string $timezone,
        public InvitationStatus $status,
        public array $people,
        public array $events,
        public array $story,
        public array $gifts,
        public array $content,
        public array $settings,
        public array $sections,
    ) {}

    public static function fromModel(Invitation $i, ?EventTypeRegistry $registry = null): self
    {
        $i->loadMissing(['people', 'events', 'storyItems', 'gifts']);
        $registry ??= app(EventTypeRegistry::class);
        $def = $registry->for($i->event_type);
        $tz = $i->timezone;

        return new self(
            $i->id,
            $i->slug,
            $i->title,
            $i->event_type,
            $i->template_slug,
            $i->locale,
            $tz,
            $i->status,
            $i->people->map(fn ($m) => PersonData::fromModel($m))->all(),
            $i->events->map(fn ($m) => EventData::fromModel($m, $tz))->all(),
            $i->storyItems->map(fn ($m) => StoryItemData::fromModel($m))->all(),
            $i->gifts->map(fn ($m) => GiftData::fromModel($m))->all(),
            // Only keys the event type defines, so stale keys never reach a template.
            array_intersect_key($i->content ?? [], array_flip($def->contentKeys())),
            $i->settings ?? [],
            $def->sections,
        );
    }

    /** First person with the given role, or null. */
    public function person(PersonRole $role): ?PersonData
    {
        foreach ($this->people as $p) {
            if ($p->role === $role) {
                return $p;
            }
        }

        return null;
    }

    /** @return list<PersonData> */
    public function peopleWithRole(PersonRole $role): array
    {
        return array_values(array_filter($this->people, fn (PersonData $p) => $p->role === $role));
    }

    public function firstEvent(): ?EventData
    {
        return $this->events[0] ?? null;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 'slug' => $this->slug, 'title' => $this->title, 'event_type' => $this->eventType->value,
            'template_slug' => $this->templateSlug, 'locale' => $this->locale, 'timezone' => $this->timezone,
            'status' => $this->status->value,
            'people' => array_map(fn (PersonData $p) => $p->toArray(), $this->people),
            'events' => array_map(fn (EventData $e) => $e->toArray(), $this->events),
            'story' => array_map(fn (StoryItemData $s) => $s->toArray(), $this->story),
            'gifts' => array_map(fn (GiftData $g) => $g->toArray(), $this->gifts),
            'content' => $this->content, 'settings' => $this->settings, 'sections' => $this->sections,
        ];
    }
}
