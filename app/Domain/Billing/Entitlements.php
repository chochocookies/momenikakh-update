<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Domain\Billing\Models\Plan;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/** DOM-10: the single place that answers "may this invitation/user do X?". No plan checks anywhere else. */
final class Entitlements
{
    public function __construct(private readonly SubscriptionResolver $resolver) {}

    /** The plan in force: the active subscription's plan, else the free plan. */
    public function planFor(Invitation|User $subject, ?CarbonInterface $at = null): Plan
    {
        $sub = $subject instanceof Invitation
            ? $this->resolver->forInvitation($subject, $at)
            : $this->resolver->forUser($subject, $at);

        return $sub?->plan ?? Plan::free();
    }

    /** True when a non-free subscription is active at the given moment. */
    public function hasActivePlan(Invitation|User $subject, ?CarbonInterface $at = null): bool
    {
        $sub = $subject instanceof Invitation
            ? $this->resolver->forInvitation($subject, $at)
            : $this->resolver->forUser($subject, $at);

        return $sub !== null && ! $sub->plan->isFree();
    }

    public function can(Invitation|User $subject, string $ability, ?CarbonInterface $at = null): bool
    {
        if (! in_array($ability, (array) config('entitlements.abilities'), true)) {
            throw new InvalidArgumentException("Unknown ability [{$ability}].");
        }

        return (bool) ($this->planFor($subject, $at)->features[$ability] ?? false);
    }

    /** null = no limit decided/applied. */
    public function limit(Invitation|User $subject, string $key, ?CarbonInterface $at = null): ?int
    {
        if (! in_array($key, (array) config('entitlements.limits'), true)) {
            throw new InvalidArgumentException("Unknown limit [{$key}].");
        }
        $value = $this->planFor($subject, $at)->limits[$key] ?? null;

        return $value === null ? null : (int) $value;
    }

    /** When a publish made at $from should expire: end of the active subscription, else free-plan duration, else never. */
    public function expiresAtFor(Invitation $invitation, ?CarbonInterface $from = null): ?CarbonImmutable
    {
        $from = CarbonImmutable::instance($from ?? now());
        $sub = $this->resolver->forInvitation($invitation, $from);
        if ($sub?->ends_at !== null) {
            return CarbonImmutable::instance($sub->ends_at);
        }
        $days = ($sub?->plan ?? Plan::free())->duration_days;

        return $days === null ? null : $from->addDays($days);
    }
}
