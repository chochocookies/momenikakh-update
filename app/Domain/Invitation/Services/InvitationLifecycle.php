<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use Carbon\CarbonInterface;

/** DOM-09: draft -> published -> expired/archived. Completeness checks (F4-10) are added in front of publish(). */
final class InvitationLifecycle
{
    public function __construct(private readonly Entitlements $entitlements) {}

    public function publish(Invitation $invitation, ?CarbonInterface $now = null): Invitation
    {
        $now ??= now();
        $this->guard($invitation, InvitationStatus::Published);
        // DEC-01: a free invitation is a sample (preview only). Only an invitation with the paid plan goes live.
        if (! $this->entitlements->can($invitation, 'publish', $now)) {
            throw PublishRequiresPlan::forSample();
        }
        $invitation->forceFill([
            'status' => InvitationStatus::Published,
            'published_at' => $invitation->published_at ?? $now,
            'expires_at' => $this->entitlements->expiresAtFor($invitation, $now),
        ])->save();

        return $invitation;
    }

    public function unpublish(Invitation $invitation): Invitation
    {
        return $this->move($invitation, InvitationStatus::Draft);
    }

    public function archive(Invitation $invitation): Invitation
    {
        return $this->move($invitation, InvitationStatus::Archived);
    }

    public function restore(Invitation $invitation): Invitation
    {
        return $this->move($invitation, InvitationStatus::Draft);
    }

    public function expire(Invitation $invitation): Invitation
    {
        return $this->move($invitation, InvitationStatus::Expired);
    }

    /** Scheduler entry point. Returns how many invitations were expired. */
    public function expireDue(?CarbonInterface $now = null): int
    {
        return Invitation::query()
            ->where('status', InvitationStatus::Published->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now ?? now())
            ->update(['status' => InvitationStatus::Expired->value]);
    }

    private function move(Invitation $invitation, InvitationStatus $to): Invitation
    {
        $this->guard($invitation, $to);
        $invitation->forceFill(['status' => $to])->save();

        return $invitation;
    }

    private function guard(Invitation $invitation, InvitationStatus $to): void
    {
        if (! $invitation->status->canTransitionTo($to)) {
            throw InvalidTransition::between($invitation->status, $to);
        }
    }
}
