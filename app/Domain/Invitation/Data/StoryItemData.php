<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Data;

use App\Domain\Invitation\Models\InvitationStoryItem;

final readonly class StoryItemData
{
    public function __construct(public int $id, public string $title, public string $body, public ?string $happenedOn) {}

    public static function fromModel(InvitationStoryItem $m): self
    {
        return new self($m->id, $m->title, $m->body, $m->happened_on?->format('Y-m-d'));
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'body' => $this->body, 'happened_on' => $this->happenedOn];
    }
}
