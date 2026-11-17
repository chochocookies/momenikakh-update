<?php

declare(strict_types=1);

namespace App\Domain\Account\Concerns;

use App\Domain\Account\Enums\UserRole;
use App\Domain\Billing\Models\Payment;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Add `use OwnsInvitations;` to App\Models\User. It registers the role cast automatically
 * (Eloquent calls initializeOwnsInvitations()), so no other edit to User is required.
 */
trait OwnsInvitations
{
    public function initializeOwnsInvitations(): void
    {
        $this->mergeCasts(['role' => UserRole::class]);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
