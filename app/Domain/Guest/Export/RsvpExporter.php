<?php

declare(strict_types=1);

namespace App\Domain\Guest\Export;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;

/** Rows for the RSVP export. Times are shown in the invitation's own timezone. */
final class RsvpExporter
{
    public const HEADERS = ['Nama', 'Kehadiran', 'Jumlah tamu', 'Catatan', 'Kelompok', 'Waktu'];

    /** @return \Generator<int,list<string|int|null>> */
    public function rows(Invitation $invitation): \Generator
    {
        $tz = $invitation->timezone ?: (string) config('guests.export.timezone_fallback', 'Asia/Jakarta');

        $query = Rsvp::query()->where('invitation_id', $invitation->id)->with('guest:id,group')->orderBy('id');
        foreach ($query->lazyById(500) as $rsvp) {
            yield [
                $rsvp->name,
                match ($rsvp->attendance) {
                    Attendance::Yes => 'Hadir',
                    Attendance::No => 'Tidak hadir',
                    Attendance::Maybe => 'Mungkin',
                },
                $rsvp->pax,
                $rsvp->note,
                $rsvp->guest?->group,
                $rsvp->created_at?->copy()->setTimezone($tz)->format('Y-m-d H:i'),
            ];
        }
    }

    public function filename(Invitation $invitation): string
    {
        return 'rsvp-'.$invitation->slug.'-'.now()->format('Ymd').'.csv';
    }
}
