<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Guest> */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => fake('id_ID')->name(),
            'group' => fake()->randomElement(['Keluarga', 'Teman', 'Rekan kerja']),
            'phone' => '08120000'.fake()->numerify('####'),
        ];
    }
}
