<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

/** DSH-10 (UU PDP): everything Momenikah holds about an owner, as one JSON-ready array. No password, token or other secret. */
final class AccountDataExporter
{
    /** @return array<string,mixed> */
    public function export(User $user): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'account' => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone, 'registered_at' => $user->created_at?->toIso8601String()],
            'invitations' => Invitation::query()->ownedBy($user)->orderBy('id')->get()->map(fn (Invitation $i) => [
                'invitation' => InvitationData::fromModel($i)->toArray(),
                'published_at' => $i->published_at?->toIso8601String(),
                'guests' => $i->guests()->orderBy('id')->get()->map(fn (Guest $g) => [
                    'name' => $g->name, 'group' => $g->group, 'phone' => $g->phone, 'opened_at' => $g->opened_at?->toIso8601String(),
                ])->all(),
                'rsvps' => $i->rsvps()->orderBy('id')->get()->map(fn (Rsvp $r) => [
                    'name' => $r->name, 'attendance' => $r->attendance->value, 'pax' => $r->pax, 'note' => $r->note, 'sent_at' => $r->created_at?->toIso8601String(),
                ])->all(),
                'wishes' => $i->messages()->orderBy('id')->get()->map(fn (GuestMessage $m) => [
                    'name' => $m->name, 'message' => $m->message, 'visible' => $m->is_visible, 'sent_at' => $m->created_at?->toIso8601String(),
                ])->all(),
            ])->all(),
        ];
    }
}
