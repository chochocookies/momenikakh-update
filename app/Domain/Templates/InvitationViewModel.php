<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Data\EventData;
use App\Domain\Invitation\Data\GiftData;
use App\Domain\Invitation\Data\PersonData;
use App\Domain\Invitation\Data\StoryItemData;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Media\Data\MediaData;
use App\Domain\Media\Data\MusicData;
use Carbon\CarbonImmutable;

/**
 * The single contract between an invitation and a template (.claude/docs/04-sistem-template.md).
 * Read-only plain data: a template never sees an Eloquent model, the request, or the signed-in user.
 * There is no guest name here on purpose: the server renders a neutral page and core.js fills `?to=` with textContent.
 */
final readonly class InvitationViewModel
{
    /**
     * @param  list<PersonData>  $people
     * @param  list<EventData>  $schedule
     * @param  list<StoryItemData>  $story
     * @param  list<GiftData>  $gifts
     * @param  list<MediaData>  $gallery
     * @param  array<int,MediaData>  $photos  person id => photo
     * @param  array<int,MediaData>  $qr  gift id => QR image
     * @param  array<string,string>  $content  only the keys the event type defines
     * @param  list<string>  $sections  active sections in render order
     * @param  array<string,mixed>  $settings
     * @param  array<string,string>  $urls  canonical, opened, rsvp, wishes
     */
    public function __construct(
        public string $slug,
        public string $title,
        public EventType $eventType,
        public string $eventLabel,
        public string $locale,
        public string $timezone,
        public string $templateSlug,
        public string $templateVersion,
        public string $palette,
        public string $motion,
        public array $people,
        public array $schedule,
        public array $story,
        public array $gifts,
        public ?MediaData $cover,
        public array $gallery,
        public array $photos,
        public array $qr,
        public ?MusicData $music,
        public array $content,
        public array $sections,
        public array $settings,
        public array $urls,
        public bool $rsvpEnabled = true,
        public bool $wishesEnabled = true,
        public bool $isPreview = false,
        public string $guestPlaceholder = 'Tamu Undangan',
    ) {}

    public function has(string $section): bool
    {
        return in_array($section, $this->sections, true);
    }

    public function person(PersonRole $role): ?PersonData
    {
        return $this->peopleWithRole($role)[0] ?? null;
    }

    /** @return list<PersonData> */
    public function peopleWithRole(PersonRole $role): array
    {
        return array_values(array_filter($this->people, fn (PersonData $p) => $p->role === $role));
    }

    public function firstEvent(): ?EventData
    {
        return $this->schedule[0] ?? null;
    }

    public function photoFor(PersonData $person): ?MediaData
    {
        return $this->photos[$person->id] ?? null;
    }

    public function qrFor(GiftData $gift): ?MediaData
    {
        return $this->qr[$gift->id] ?? null;
    }

    /** An owner-written text of this event type (opening, quote, prayer ...), or null when empty. */
    public function text(string $key): ?string
    {
        $value = $this->content[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /** ISO 8601 instant (with the event's UTC offset) the countdown runs to, or null without a schedule. */
    public function countdownTo(): ?string
    {
        return $this->firstEvent()?->startsAt->toIso8601String();
    }

    /** "Resepsi": the owner's label, else the default name of the event kind. */
    public function eventTitle(EventData $event): string
    {
        return Labels::event($event);
    }

    public function roleLabel(PersonData $person): string
    {
        return Labels::role($person->role);
    }

    public function peopleHeading(): string
    {
        return Labels::peopleHeading($this->eventType);
    }

    /** "Minggu, 14 Maret 2027" in Indonesian. */
    public function dateLabel(EventData $event): string
    {
        return Labels::date($event->startsAt);
    }

    /** "14 Maret 2027" for a birth card, or null when the person has no birth date or it does not apply. */
    public function birthLabel(PersonData $person): ?string
    {
        if ($person->birthDate === null || ! in_array($person->role, [PersonRole::Baby, PersonRole::Child], true)) {
            return null;
        }

        return Labels::date(CarbonImmutable::parse($person->birthDate));
    }

    /** "Mei 2022" */
    public function storyDateLabel(StoryItemData $item): ?string
    {
        return $item->happenedOn === null ? null : Labels::monthYear($item->happenedOn);
    }

    /** "09.00 – 10.30 WIB" */
    public function timeLabel(EventData $event): string
    {
        return Labels::time($event, $this->timezone);
    }

    /** Whether the template should ask guests to open a map. */
    public function hasMap(): bool
    {
        foreach ($this->schedule as $event) {
            if ($event->mapsUrl !== null) {
                return true;
            }
        }

        return false;
    }
}
