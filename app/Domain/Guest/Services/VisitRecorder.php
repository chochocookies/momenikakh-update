<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Facades\Cache;

/**
 * PUB-06: daily numbers for an invitation. Counted from a beacon the page sends after it loaded (so edge-cached pages
 * count too). The page runs script, so crawlers and link-preview fetchers rarely arrive here; the ones that do are still
 * dropped by user agent. Previews never send the beacon and the invitation must be published.
 *
 * - views: every visit of a person.
 * - unique_visitors: once per visitor per day. A visitor is a salted hash of address and browser, kept in the cache for
 *   a day. No cookie and nothing personal in the database.
 * - opens: once per visitor per day, when the cover is opened.
 */
final class VisitRecorder
{
    private const BOT = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|headless|lighthouse|curl|wget|python|httpclient|monitor|uptime|scrapy/i';

    public function record(string $slug, string $event, string $ip, ?string $userAgent): bool
    {
        if (! in_array($event, ['view', 'open'], true) || $this->isBot($userAgent)) {
            return false;
        }
        $invitation = Invitation::query()->published()->where('slug', $slug)->first(['id']);
        if ($invitation === null) {
            return false;
        }

        $today = now()->toDateString();
        $visitor = hash('sha256', config('app.key').'|'.$ip.'|'.$userAgent.'|'.$today);
        $firstToday = Cache::add("visit:{$invitation->id}:{$today}:{$visitor}", 1, now()->addDay());
        $firstOpen = $event === 'open' && Cache::add("open:{$invitation->id}:{$today}:{$visitor}", 1, now()->addDay());

        $add = array_filter(['views' => $event === 'view' ? 1 : 0, 'unique_visitors' => $event === 'view' && $firstToday ? 1 : 0, 'opens' => $firstOpen ? 1 : 0]);
        if ($add !== []) {
            // Create today's row if it is missing (the unique index makes a concurrent insert harmless), then add atomically.
            InvitationDailyStat::query()->insertOrIgnore(['invitation_id' => $invitation->id, 'date' => $today, 'views' => 0, 'unique_visitors' => 0, 'opens' => 0, 'created_at' => now(), 'updated_at' => now()]);
            InvitationDailyStat::query()->where(['invitation_id' => $invitation->id, 'date' => $today])->incrementEach($add);
        }

        return true;
    }

    private function isBot(?string $userAgent): bool
    {
        return $userAgent === null || trim($userAgent) === '' || preg_match(self::BOT, $userAgent) === 1;
    }
}
