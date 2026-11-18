<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rsvp> */
class RsvpFactory extends Factory
{
    protected $model = Rsvp::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => fake('id_ID')->name(),
            'attendance' => fake()->randomElement(Attendance::cases()),
            'pax' => fake()->numberBetween(1, 4),
        ];
    }
}
