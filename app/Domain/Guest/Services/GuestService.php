<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Billing\Entitlements;
use App\Domain\Guest\Exceptions\GuestLimitReached;
use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Str;

/** Manual guest management. The guest limit always comes from Entitlements (DOM-10). */
final class GuestService
{
    public function __construct(private readonly PhoneNormalizer $phones, private readonly Entitlements $entitlements) {}

    /** @param array{name:string,group?:?string,phone?:?string} $data validated data */
    public function add(Invitation $invitation, array $data): Guest
    {
        $this->assertCapacity($invitation, 1);

        return $invitation->guests()->create([
            'name' => TextCleaner::singleLine($data['name']),
            'group' => ($g = TextCleaner::singleLine((string) ($data['group'] ?? ''))) === '' ? null : $g,
            'phone' => $this->phones->normalize($data['phone'] ?? null),
        ]);
    }

    public function assertCapacity(Invitation $invitation, int $adding): void
    {
        $limit = $this->entitlements->limit($invitation, 'max_guests');
        if ($limit === null) {
            return;
        }
        $current = $invitation->guests()->count();
        if ($current + $adding > $limit) {
            throw GuestLimitReached::for($limit, $current, $adding);
        }
    }

    /** Use when a link was shared by mistake: the old `g` token stops working. */
    public function regenerateToken(Guest $guest): Guest
    {
        $guest->forceFill(['token' => Str::lower(Str::random(12)), 'opened_at' => null])->save();

        return $guest;
    }
}
