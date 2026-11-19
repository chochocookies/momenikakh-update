<?php

declare(strict_types=1);

namespace App\Domain\Guest\Policies;

use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Models\User;

/** Guest, Rsvp and GuestMessage belong to whoever owns their invitation. */
class InvitationChildPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Guest|GuestMessage|Rsvp $model): bool
    {
        return $this->owns($user, $model);
    }

    public function update(User $user, Guest|GuestMessage|Rsvp $model): bool
    {
        return $this->owns($user, $model);
    }

    public function delete(User $user, Guest|GuestMessage|Rsvp $model): bool
    {
        return $this->owns($user, $model);
    }

    private function owns(User $user, Guest|GuestMessage|Rsvp $model): bool
    {
        // loadMissing is explicit eager loading, so it is allowed even with preventLazyLoading().
        $model->loadMissing('invitation');

        return $model->invitation !== null && $model->invitation->isOwnedBy($user);
    }
}
