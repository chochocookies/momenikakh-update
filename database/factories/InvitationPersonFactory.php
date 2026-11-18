<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvitationPerson> */
class InvitationPersonFactory extends Factory
{
    protected $model = InvitationPerson::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'role' => PersonRole::Groom,
            'name' => fake('id_ID')->name(),
            'nickname' => fake('id_ID')->firstName(),
            'sort' => 0,
        ];
    }
}
