<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Enums\InvitationStatus;

/**
 * GST-02: records "this guest opened the invitation" from the `g` token. Idempotent: only the first open is stored.
 * The token is not a secret and grants nothing; at worst someone marks a guest as "opened".
 */
final class GuestOpenedRecorder
{
    public function record(string $slug, string $token): bool
    {
        if (preg_match('/^[a-z0-9]{12}$/', $token) !== 1) {
            return false;
        }

        $guest = Guest::query()
            ->where('token', $token)
            ->whereHas('invitation', fn ($q) => $q->where('slug', $slug)->where('status', InvitationStatus::Published->value))
            ->first(['id', 'opened_at']);
        if ($guest === null) {
            return false;
        }
        if ($guest->opened_at === null) {
            Guest::query()->whereKey($guest->id)->whereNull('opened_at')->update(['opened_at' => now()]);
        }

        return true;
    }
}
