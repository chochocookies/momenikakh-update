<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Billing\Enums\PlanTier;
use App\Domain\Billing\Models\Plan;
use Illuminate\Database\Seeder;

/** Safe in every environment. Prices stay null until DEC-01 decides them. */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = (array) config('entitlements.free');
        Plan::query()->updateOrCreate(['key' => 'free'], [
            'name' => $free['name'], 'tier' => PlanTier::Free, 'price_idr' => null, 'duration_days' => $free['duration_days'],
            'features' => $free['features'], 'limits' => $free['limits'], 'is_active' => true, 'sort' => 0,
        ]);

        // Placeholder premium plan, inactive so it can never be sold before DEC-01.
        Plan::query()->updateOrCreate(['key' => 'premium'], [
            'name' => 'Premium (placeholder)', 'tier' => PlanTier::Premium, 'price_idr' => null, 'duration_days' => null,
            'features' => array_fill_keys((array) config('entitlements.abilities'), true),
            'limits' => array_fill_keys((array) config('entitlements.limits'), null),
            'is_active' => false, 'sort' => 1,
        ]);
    }
}
