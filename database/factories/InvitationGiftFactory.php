<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Invitation\Enums\GiftType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvitationGift> */
class InvitationGiftFactory extends Factory
{
    protected $model = InvitationGift::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'type' => GiftType::Bank,
            'provider' => 'Bank Contoh',
            'holder_name' => fake('id_ID')->name(),
            'number' => '1234567890',
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
