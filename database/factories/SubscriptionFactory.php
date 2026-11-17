<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'invitation_id' => null,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ];
    }

    /** Attach to one invitation (and its owner). */
    public function forInvitation(Invitation $invitation): static
    {
        return $this->state(fn () => ['user_id' => $invitation->user_id, 'invitation_id' => $invitation->id]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::Active, 'starts_at' => now()->subDays(60), 'ends_at' => now()->subDay()]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => SubscriptionStatus::Pending]);
    }

    public function lifetime(): static
    {
        return $this->state(fn () => ['ends_at' => null]);
    }
}
