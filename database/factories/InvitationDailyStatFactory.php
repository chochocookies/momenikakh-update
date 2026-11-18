<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvitationDailyStat> */
class InvitationDailyStatFactory extends Factory
{
    protected $model = InvitationDailyStat::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'date' => fake()->unique()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'views' => fake()->numberBetween(0, 200),
            'unique_visitors' => fake()->numberBetween(0, 100),
            'opens' => fake()->numberBetween(0, 100),
        ];
    }
}
