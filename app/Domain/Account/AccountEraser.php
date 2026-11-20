<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Audit\AuditLogger;
use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * DSH-10 (UU PDP): deletes an owner and everything under them. Invitations are force-deleted one by one so the media
 * library removes their files; guests, RSVPs, wishes, people, events and the rest go with them through the foreign keys.
 * The audit log keeps one row without any personal data (the count of invitations).
 */
final class AccountEraser
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function erase(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $count = 0;
            Invitation::query()->withTrashed()->where('user_id', $user->id)->each(function (Invitation $invitation) use (&$count): void {
                $invitation->forceDelete();
                $count++;
            });
            $this->audit->record(AuditAction::InvitationDeleted, null, null, null, ['count' => $count, 'reason' => 'account']);
            $user->delete();
        });
    }
}
