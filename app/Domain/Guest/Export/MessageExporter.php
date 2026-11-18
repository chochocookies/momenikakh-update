<?php

declare(strict_types=1);

namespace App\Domain\Guest\Export;

use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Invitation\Models\Invitation;

final class MessageExporter
{
    public const HEADERS = ['Nama', 'Ucapan', 'Ditampilkan', 'Waktu'];

    /** @return \Generator<int,list<string|bool|null>> */
    public function rows(Invitation $invitation): \Generator
    {
        $tz = $invitation->timezone ?: (string) config('guests.export.timezone_fallback', 'Asia/Jakarta');

        foreach (GuestMessage::query()->where('invitation_id', $invitation->id)->orderBy('id')->lazyById(500) as $m) {
            yield [$m->name, $m->message, (bool) $m->is_visible, $m->created_at?->copy()->setTimezone($tz)->format('Y-m-d H:i')];
        }
    }

    public function filename(Invitation $invitation): string
    {
        return 'ucapan-'.$invitation->slug.'-'.now()->format('Ymd').'.csv';
    }
}
