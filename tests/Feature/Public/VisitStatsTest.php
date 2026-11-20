<?php

declare(strict_types=1);

use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Surfaces;

const PHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';
const OTHER_PHONE = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/126.0 Mobile Safari/537.36';

beforeEach(function () {
    $this->withoutVite();
    Cache::flush();
    $this->invitation = Invitation::factory()->wedding()->complete()->published()->create(['slug' => 'raka-sinta-abcd']);
    $this->seen = fn (string $event = 'view', string $ua = PHONE, string $ip = '203.0.113.10', ?string $slug = null) => $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->withHeader('User-Agent', $ua)->post(Surfaces::url('public', ($slug ?? 'raka-sinta-abcd').'/seen'), ['event' => $event]);
    $this->stat = fn () => InvitationDailyStat::query()->where('invitation_id', $this->invitation->id)->first();
});

it('counts a visitor once a day as unique, and every visit as a view', function () {
    ($this->seen)()->assertNoContent();
    ($this->seen)()->assertNoContent();
    ($this->seen)()->assertNoContent();

    expect(($this->stat)()->views)->toBe(3)->and(($this->stat)()->unique_visitors)->toBe(1);
});

it('counts two different visitors as two', function () {
    ($this->seen)();
    ($this->seen)('view', OTHER_PHONE, '198.51.100.7');

    expect(($this->stat)()->unique_visitors)->toBe(2)->and(($this->stat)()->views)->toBe(2);
});

it('counts a new day as a new visitor', function () {
    ($this->seen)();
    $this->travel(1)->days();
    ($this->seen)();

    expect(InvitationDailyStat::query()->where('invitation_id', $this->invitation->id)->orderBy('date')->pluck('unique_visitors')->all())->toBe([1, 1]);
});

it('counts a cover open once a day per visitor', function () {
    ($this->seen)('view');
    ($this->seen)('open');
    ($this->seen)('open');

    expect(($this->stat)()->opens)->toBe(1)->and(($this->stat)()->views)->toBe(1);
});

it('does not count bots, link-preview fetchers or a missing user agent', function (string $ua) {
    ($this->seen)('view', $ua)->assertNoContent();

    expect(($this->stat)())->toBeNull();
})->with([
    'googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'whatsapp' => 'WhatsApp/2.23.20 A',
    'facebook' => 'facebookexternalhit/1.1',
    'headless' => 'Mozilla/5.0 HeadlessChrome/120.0',
    'curl' => 'curl/8.4.0',
    'empty' => '',
]);

it('does not count a draft, an expired or an unknown invitation, and always answers 204', function () {
    Invitation::factory()->wedding()->complete()->create(['slug' => 'masih-draf']);
    Invitation::factory()->wedding()->complete()->expired()->create(['slug' => 'sudah-habis']);

    foreach (['masih-draf', 'sudah-habis', 'tidak-ada'] as $slug) {
        ($this->seen)('view', PHONE, '203.0.113.10', $slug)->assertNoContent();
    }

    expect(InvitationDailyStat::query()->count())->toBe(0);
});

it('ignores an event that is not view or open', function () {
    ($this->seen)('purchase')->assertNoContent();

    expect(($this->stat)())->toBeNull();
});

it('does not count the template preview: the page script only reports from the invitation path', function () {
    $js = file_get_contents(base_path('resources/js/public/stats.js'));

    expect($js)->toContain("match[1] === 'preview'")->toContain('SLUG_PATH');
    $this->get(Surfaces::url('public', 'preview/classic-elegant'))->assertOk();
    expect(InvitationDailyStat::query()->count())->toBe(0);
});

it('stays cookie-free', function () {
    expect(($this->seen)()->headers->has('Set-Cookie'))->toBeFalse();
});
