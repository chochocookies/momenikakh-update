<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationReadiness;
use App\Models\User;

/**
 * DSH-14: the short "start here" list for a new owner. It follows what the owner has really done, so a step is checked
 * by the data, not by a click. It disappears when the owner closes it or has a published invitation.
 */
final class Onboarding
{
    public function __construct(private readonly InvitationReadiness $readiness) {}

    /**
     * @return list<array{label:string,done:bool,route:string,params:array<string,mixed>}>|null null = do not show
     */
    public function steps(User $user): ?array
    {
        if ($user->onboarding_dismissed_at !== null || Invitation::query()->ownedBy($user)->published()->exists()) {
            return null;
        }

        $first = Invitation::query()->ownedBy($user)->oldest('id')->first();
        $steps = [['label' => 'Buat undangan pertamamu', 'done' => $first !== null, 'route' => 'dashboard.invitations.create', 'params' => []]];
        if ($first !== null) {
            $steps[] = ['label' => 'Isi datanya', 'done' => $this->readiness->check($first)['ready'], 'route' => 'dashboard.invitations.edit', 'params' => ['invitation' => $first]];
            $steps[] = ['label' => 'Tambahkan tamu', 'done' => $first->guests()->exists(), 'route' => 'dashboard.guests.index', 'params' => ['invitation' => $first]];
            $steps[] = ['label' => 'Terbitkan dan bagikan', 'done' => false, 'route' => 'dashboard.invitations.show', 'params' => ['invitation' => $first]];
        }

        return $steps;
    }

    public function dismiss(User $user): void
    {
        $user->forceFill(['onboarding_dismissed_at' => now()])->save();
    }
}
