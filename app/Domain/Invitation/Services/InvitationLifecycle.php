<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Audit\AuditLogger;
use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

/** DOM-09: draft -> published -> expired/archived. Completeness checks (F4-10) are added in front of publish(). */
final class InvitationLifecycle
{
    public function __construct(private readonly Entitlements $entitlements, private readonly AuditLogger $audit) {}

    public function publish(Invitation $invitation, ?CarbonInterface $now = null, ?User $by = null): Invitation
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
        $this->audit->record(AuditAction::InvitationPublished, $by, $invitation, $invitation->id, ['template' => $invitation->template_slug]);

        // Invalidate cache (PUB-03)
        $this->invalidateInvitationCache($invitation->slug);

        return $invitation;
    }

    public function unpublish(Invitation $invitation, ?User $by = null): Invitation
    {
        $result = $this->move($invitation, InvitationStatus::Draft, AuditAction::InvitationUnpublished, $by);
        $this->invalidateInvitationCache($invitation->slug);

        return $result;
    }

    public function archive(Invitation $invitation, ?User $by = null): Invitation
    {
        $result = $this->move($invitation, InvitationStatus::Archived, AuditAction::InvitationArchived, $by);
        $this->invalidateInvitationCache($invitation->slug);

        return $result;
    }

    public function restore(Invitation $invitation, ?User $by = null): Invitation
    {
        $result = $this->move($invitation, InvitationStatus::Draft, AuditAction::InvitationRestored, $by);
        $this->invalidateInvitationCache($invitation->slug);

        return $result;
    }

    public function expire(Invitation $invitation, ?User $by = null): Invitation
    {
        $result = $this->move($invitation, InvitationStatus::Expired, AuditAction::InvitationExpired, $by);
        $this->invalidateInvitationCache($invitation->slug);

        return $result;
    }

    /** Scheduler entry point. Returns how many invitations were expired. */
    public function expireDue(?CarbonInterface $now = null): int
    {
        $slugs = Invitation::query()
            ->where('status', InvitationStatus::Published->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now ?? now())
            ->pluck('slug')
            ->all();

        $count = Invitation::query()
            ->where('status', InvitationStatus::Published->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now ?? now())
            ->update(['status' => InvitationStatus::Expired->value]);

        // Invalidate cache for all expired invitations (PUB-03)
        foreach ($slugs as $slug) {
            $this->invalidateInvitationCache($slug);
        }

        // One row for the whole sweep: the scheduler is the actor (no user).
        if ($count > 0) {
            $this->audit->record(AuditAction::InvitationExpired, null, null, null, ['count' => $count, 'reason' => 'due']);
        }

        return $count;
    }

    private function move(Invitation $invitation, InvitationStatus $to, AuditAction $action, ?User $by): Invitation
    {
        $this->guard($invitation, $to);
        $from = $invitation->status;
        $invitation->forceFill(['status' => $to])->save();
        $this->audit->record($action, $by, $invitation, $invitation->id, ['from' => $from, 'to' => $to]);

        return $invitation;
    }

    private function guard(Invitation $invitation, InvitationStatus $to): void
    {
        if (! $invitation->status->canTransitionTo($to)) {
            throw InvalidTransition::between($invitation->status, $to);
        }
    }

    /**
     * Invalidate the invitation cache (PUB-03).
     */
    private function invalidateInvitationCache(string $slug): void
    {
        Cache::store('redis')->forget("invitation:{$slug}");
    }
}
