<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Domain\Billing\Models\Plan;

/**
 * The two plans of DEC-01 as the pages show them: the free sample and the Paket Template. Names and prices come from
 * the plans table, never from a view. If the table cannot be read, config/entitlements.php answers instead.
 */
final class PlanCatalog
{
    /** @return array{free:array{name:string,price:int},paid:array{name:string,price:int}} */
    public function summary(): array
    {
        /** @var array<string,Plan> $rows */
        $rows = rescue(fn () => Plan::query()->whereIn('key', [config('entitlements.free.key'), config('entitlements.paid.key')])->get()->keyBy('key')->all(), [], false);

        $pick = function (string $which) use ($rows): array {
            $config = (array) config("entitlements.{$which}");
            $row = $rows[$config['key']] ?? null;

            return $row instanceof Plan
                ? ['name' => $row->name, 'price' => (int) $row->price_idr]
                : ['name' => (string) $config['name'], 'price' => (int) $config['price_idr']];
        };

        return ['free' => $pick('free'), 'paid' => $pick('paid')];
    }
}
