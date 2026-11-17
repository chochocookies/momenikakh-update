<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Isolates the DEC-01 choice "does a plan belong to an invitation or to an account?".
 * Today: an invitation-level subscription wins, then an account-level one (invitation_id null).
 * Change only this class if DEC-01 decides otherwise.
 */
final class SubscriptionResolver
{
    public function forInvitation(Invitation $invitation, ?CarbonInterface $at = null): ?Subscription
    {
        $at ??= now();

        return Subscription::query()->active($at)->with('plan')
            ->where('user_id', $invitation->user_id)
            ->where(fn ($q) => $q->where('invitation_id', $invitation->id)->orWhereNull('invitation_id'))
            ->orderByRaw('invitation_id is null') // invitation-level first
            ->orderByDesc('ends_at')
            ->first();
    }

    public function forUser(User $user, ?CarbonInterface $at = null): ?Subscription
    {
        return Subscription::query()->active($at ?? now())->with('plan')
            ->where('user_id', $user->getKey())
            ->orderByDesc('ends_at')
            ->first();
    }
}
