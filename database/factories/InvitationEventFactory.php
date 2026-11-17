<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationEvent;
use App\Domain\Invitation\Enums\EventKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvitationEvent> */
class InvitationEventFactory extends Factory
{
    protected $model = InvitationEvent::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'kind' => EventKind::Akad,
            'starts_at' => now()->addMonths(3)->setTime(9, 0),
            'ends_at' => now()->addMonths(3)->setTime(11, 0),
            'venue_name' => 'Gedung Contoh',
            'address' => 'Jl. Contoh No. 1, Kota Contoh',
            'sort' => 0,
        ];
    }
}
