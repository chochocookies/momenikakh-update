<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Data;

use App\Domain\Invitation\Enums\EventKind;
use App\Domain\Invitation\Models\InvitationEvent;
use Carbon\CarbonImmutable;

final readonly class EventData
{
    public function __construct(
        public int $id,
        public EventKind $kind,
        public ?string $label,
        public CarbonImmutable $startsAt,   // already in the invitation timezone
        public ?CarbonImmutable $endsAt,
        public string $venueName,
        public string $address,
        public ?string $mapsUrl,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $dressCode,
        public ?string $notes,
    ) {}

    public static function fromModel(InvitationEvent $m, string $timezone): self
    {
        return new self(
            $m->id,
            $m->kind,
            $m->label,
            CarbonImmutable::instance($m->starts_at)->setTimezone($timezone),
            $m->ends_at ? CarbonImmutable::instance($m->ends_at)->setTimezone($timezone) : null,
            $m->venue_name,
            $m->address,
            $m->maps_url,
            $m->latitude,
            $m->longitude,
            $m->dress_code,
            $m->notes,
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 'kind' => $this->kind->value, 'label' => $this->label,
            'starts_at' => $this->startsAt->format('Y-m-d H:i'),
            'ends_at' => $this->endsAt?->format('Y-m-d H:i'),
            'venue_name' => $this->venueName, 'address' => $this->address, 'maps_url' => $this->mapsUrl,
            'latitude' => $this->latitude, 'longitude' => $this->longitude,
            'dress_code' => $this->dressCode, 'notes' => $this->notes,
        ];
    }
}
