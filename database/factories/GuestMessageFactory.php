<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GuestMessage> */
class GuestMessageFactory extends Factory
{
    protected $model = GuestMessage::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'name' => fake('id_ID')->name(),
            'message' => 'Selamat berbahagia, semoga acaranya lancar.',
            'is_visible' => true,
        ];
    }
}
