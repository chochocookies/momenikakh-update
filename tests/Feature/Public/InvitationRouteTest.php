<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Testing\TestResponse;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->page = fn (string $slug, string $query = '') => $this->get(Surfaces::url('public', $slug).$query);
    $this->invitation = Invitation::factory()->wedding()->complete()->published()->create(['slug' => 'raka-sinta-abcd', 'title' => 'Raka & Sinta']);
});

/** The PUB-01 criterion: a response without any Set-Cookie header. */
function expectNoCookies(TestResponse $res): void
{
    expect($res->headers->has('Set-Cookie'))->toBeFalse('Set-Cookie header present')
        ->and($res->headers->getCookies())->toBe([]);
}

it('serves a published invitation at /{slug} with its template', function () {
    $res = ($this->page)('raka-sinta-abcd')->assertOk();
    $xp = DesignSystem::xpath($res->getContent());

    expect($res->getContent())->toContain('data-theme="classic-elegant"')->toContain('Raka &amp; Sinta')
        ->and($xp->query('//h1')->length)->toBe(1)
        ->and($xp->query('//*[@id="people"]')->length)->toBe(1);
});

it('answers without Set-Cookie: no session cookie and no CSRF cookie', function () {
    expectNoCookies(($this->page)('raka-sinta-abcd')->assertOk());
});

it('stays cookie-free when the visitor already sends cookies', function () {
    $res = $this->withUnencryptedCookies(['laravel_session' => 'abc', 'XSRF-TOKEN' => 'x'])->get(Surfaces::url('public', 'raka-sinta-abcd'))->assertOk();

    expectNoCookies($res);
});

it('keeps every public route cookie-free: preview, beacon, home and 404', function () {
    expectNoCookies(($this->page)('preview/classic-elegant')->assertOk());
    expectNoCookies(($this->page)('preview/classic-elegant/frame')->assertOk());
    expectNoCookies(($this->page)('')->assertOk());
    expectNoCookies(($this->page)('no-such-invitation')->assertNotFound());
    expectNoCookies($this->post(Surfaces::url('public', 'raka-sinta-abcd/opened'), ['g' => 'abcdefghij12'])->assertNoContent());
});

it('does not need a CSRF token to receive a guest action', function () {
    $this->post(Surfaces::url('public', 'raka-sinta-abcd/opened'), ['g' => 'abcdefghij12'])->assertNoContent();
});

it('keeps the other surfaces stateful', function () {
    expect(($this->get(Surfaces::url('marketing', '/')))->headers->has('Set-Cookie'))->toBeTrue();
});

it('answers 404 for a draft and an archived invitation', function (string $state) {
    Invitation::factory()->wedding()->{$state}()->create(['slug' => "slug-{$state}"]);

    ($this->page)("slug-{$state}")->assertNotFound();
})->with(['draft', 'archived']);

it('answers 410 with a friendly page for an expired invitation, and 404 with one for an unknown slug', function () {
    Invitation::factory()->wedding()->expired()->create(['slug' => 'slug-expired']);

    $gone = ($this->page)('slug-expired')->assertStatus(410);
    $missing = ($this->page)('does-not-exist')->assertNotFound();

    expect($gone->getContent())->toContain('Undangan ini sudah berakhir')->toContain('noindex')
        ->and($missing->getContent())->toContain('Undangan tidak ditemukan')->toContain('noindex')->not->toContain('Tidak ada');
    expectNoCookies($gone);
});

it('answers 404 for an unknown slug and never reveals which slugs exist', function () {
    ($this->page)('does-not-exist')->assertNotFound();
});

it('does not match slugs that are not kebab-case or that are reserved', function (string $slug) {
    ($this->page)($slug)->assertNotFound();
})->with(['UPPER', 'with_underscore', 'admin', 'login', 'dashboard', 'styleguide']);

it('never renders the guest name on the server', function () {
    $html = ($this->page)('raka-sinta-abcd', '?to='.urlencode('Budi Santoso <b>').'&g=abcdefghij12')->assertOk()->getContent();

    expect($html)->not->toContain('Budi Santoso')->toContain('data-guest-default="Tamu Undangan"');
});

it('lets the preview and the beacon keep working next to the catch-all', function () {
    ($this->page)('preview/classic-elegant')->assertOk();
    ($this->page)('preview')->assertNotFound();
});

it('keeps /up and /health answering on the public host', function () {
    ($this->page)('up')->assertOk();
    $res = ($this->page)('health');
    expect($res->status())->toBeIn([200, 503])->and($res->json('checks'))->toBeArray();
});

it('serves a published invitation to the second template without changing the route', function () {
    $this->invitation->update(['template_slug' => 'does-not-exist-anymore']);

    ($this->page)('raka-sinta-abcd')->assertOk()->assertSee('data-theme="classic-elegant"', false); // falls back to the default template
});

it('marks the invitation noindex twice (header and meta) and carries title, og tags and theme colour, never a guest name', function () {
    $res = ($this->page)('raka-sinta-abcd', '?to='.urlencode('Budi Santoso'))->assertOk();
    $html = $res->getContent();

    expect($res->headers->get('X-Robots-Tag'))->toBe('noindex, nofollow')
        ->and($html)->toContain('<meta name="robots" content="noindex')
        ->toContain('<title>Raka &amp; Sinta')->toContain('property="og:title"')->toContain('property="og:image"')->toContain('name="theme-color"')
        ->toContain('rel="canonical" href="'.Surfaces::url('public', 'raka-sinta-abcd').'"')
        ->not->toContain('Budi');
});
