<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Billing\Enums\PlanTier;
use App\Domain\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Plan> */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'key' => 'plan-'.fake()->unique()->bothify('????##'),
            'name' => 'Paket Contoh',
            'tier' => PlanTier::Premium,
            'price_idr' => null,            // decided by DEC-01
            'duration_days' => 30,
            'features' => array_fill_keys((array) config('entitlements.abilities'), true),
            'limits' => array_fill_keys((array) config('entitlements.limits'), null),
            'is_active' => true,
            'sort' => 1,
        ];
    }

    public function free(): static
    {
        $free = (array) config('entitlements.free');

        return $this->state(fn () => [
            'key' => 'free', 'name' => $free['name'], 'tier' => PlanTier::Free, 'duration_days' => $free['duration_days'],
            'features' => $free['features'], 'limits' => $free['limits'], 'sort' => 0,
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn () => ['tier' => PlanTier::Premium]);
    }

    /** @param array<string,bool> $features */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (array $a) => ['features' => $features + $a['features']]);
    }

    /** @param array<string,int|null> $limits */
    public function withLimits(array $limits): static
    {
        return $this->state(fn (array $a) => ['limits' => $limits + $a['limits']]);
    }
}
