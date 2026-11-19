<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Billing\Enums\PlanTier;
use App\Domain\Billing\Models\Plan;
use Illuminate\Database\Seeder;

/** Safe in every environment. The plans come from config/entitlements.php (DEC-01), so prices live in one place. */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['free', 0], ['paid', 1]] as [$config, $sort]) {
            $plan = (array) config("entitlements.{$config}");
            Plan::query()->updateOrCreate(['key' => $plan['key']], [
                'name' => $plan['name'], 'tier' => PlanTier::from($plan['tier']), 'price_idr' => $plan['price_idr'],
                'duration_days' => $plan['duration_days'], 'features' => $plan['features'], 'limits' => $plan['limits'],
                'is_active' => true, 'sort' => $sort,
            ]);
        }

        // The old inactive placeholder from before DEC-01 is replaced by "paket-template". Keep it if anyone subscribed.
        Plan::query()->where('key', 'premium')->whereDoesntHave('subscriptions')->delete();
    }
}
