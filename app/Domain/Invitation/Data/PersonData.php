<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Data;

use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\Models\InvitationPerson;

final readonly class PersonData
{
    public function __construct(
        public int $id,
        public PersonRole $role,
        public string $name,
        public ?string $nickname,
        public ?string $parentsText,
        public ?string $bio,
        public ?string $birthDate,
        public ?string $instagram,
    ) {}

    public static function fromModel(InvitationPerson $m): self
    {
        return new self(
            $m->id,
            $m->role,
            $m->name,
            $m->nickname,
            $m->parents_text,
            $m->bio,
            $m->birth_date?->format('Y-m-d'),
            $m->instagram,
        );
    }

    /** @return array<string,mixed> same keys the editor sends */
    public function toArray(): array
    {
        return [
            'id' => $this->id, 'role' => $this->role->value, 'name' => $this->name, 'nickname' => $this->nickname,
            'parents_text' => $this->parentsText, 'bio' => $this->bio, 'birth_date' => $this->birthDate, 'instagram' => $this->instagram,
        ];
    }
}
