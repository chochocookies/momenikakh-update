<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Policies;

use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

/** DOM-08: ownership. Admins pass every ability through before(). */
class InvitationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true; // lists are always narrowed with Invitation::ownedBy($user)
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }

    public function publish(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }

    public function manageGuests(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }

    public function moderate(User $user, Invitation $invitation): bool
    {
        return $invitation->isOwnedBy($user);
    }
}
