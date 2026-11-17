<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationStoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvitationStoryItem> */
class InvitationStoryItemFactory extends Factory
{
    protected $model = InvitationStoryItem::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'title' => fake('id_ID')->sentence(3),
            'body' => fake('id_ID')->sentence(12),
            'happened_on' => fake()->date(),
            'sort' => 0,
        ];
    }
}
