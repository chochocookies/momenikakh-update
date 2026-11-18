<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Data;

use App\Domain\Invitation\Enums\GiftType;
use App\Domain\Invitation\Models\InvitationGift;

final readonly class GiftData
{
    public function __construct(
        public int $id,
        public GiftType $type,
        public ?string $provider,
        public ?string $holderName,
        public ?string $number,
        public ?string $addressText,
        public bool $isActive,
    ) {}

    public static function fromModel(InvitationGift $m): self
    {
        return new self($m->id, $m->type, $m->provider, $m->holder_name, $m->number, $m->address_text, (bool) $m->is_active);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 'type' => $this->type->value, 'provider' => $this->provider, 'holder_name' => $this->holderName,
            'number' => $this->number, 'address_text' => $this->addressText, 'is_active' => $this->isActive,
        ];
    }
}
