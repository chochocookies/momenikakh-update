<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Invitation\Models\Invitation;

/** DSH-07: the numbers the owner sees for one invitation: visits of the last N days, and what guests answered so far. */
final class StatsSummary
{
    public const DAYS = 30;

    /**
     * @return array{days:int,unique_visitors:int,views:int,opens:int,rsvps:int,attending_people:int,wishes:int,daily:list<array{date:string,unique_visitors:int,views:int}>}
     */
    public function for(Invitation $invitation, int $days = self::DAYS): array
    {
        $since = now()->subDays($days - 1)->toDateString();
        $rows = InvitationDailyStat::query()->where('invitation_id', $invitation->id)->where('date', '>=', $since)->orderByDesc('date')->get();

        return [
            'days' => $days,
            'unique_visitors' => (int) $rows->sum('unique_visitors'),
            'views' => (int) $rows->sum('views'),
            'opens' => (int) $rows->sum('opens'),
            'rsvps' => $invitation->rsvps()->count(),
            'attending_people' => (int) $invitation->rsvps()->where('attendance', 'yes')->sum('pax'),
            'wishes' => $invitation->messages()->where('is_visible', true)->count(),
            'daily' => $rows->take(7)->map(fn (InvitationDailyStat $r) => ['date' => $r->date->toDateString(), 'unique_visitors' => $r->unique_visitors, 'views' => $r->views])->values()->all(),
        ];
    }
}
