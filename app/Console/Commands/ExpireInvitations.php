<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Invitation\Services\InvitationLifecycle;
use Illuminate\Console\Command;

final class ExpireInvitations extends Command
{
    protected $signature = 'invitations:expire';

    protected $description = 'Mark published invitations whose expiry time has passed as expired';

    public function handle(InvitationLifecycle $lifecycle): int
    {
        $count = $lifecycle->expireDue();
        $this->info("Expired {$count} invitation(s).");

        return self::SUCCESS;
    }
}
