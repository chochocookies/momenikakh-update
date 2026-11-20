<?php

declare(strict_types=1);

use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->invitation = Invitation::factory()->wedding()->complete()->published()->create(['slug' => 'raka-sinta-abcd']);
    $this->url = fn (string $path, ?string $slug = null) => Surfaces::url('public', ($slug ?? 'raka-sinta-abcd').'/'.$path);
    $this->rsvp = fn (array $data = [], ?string $slug = null) => $this->postJson(($this->url)('rsvp', $slug), $data + ['name' => 'Budi', 'attendance' => 'yes', 'pax' => 2]);
    $this->wish = fn (array $data = [], ?string $slug = null) => $this->postJson(($this->url)('wishes', $slug), $data + ['name' => 'Budi', 'message' => 'Selamat menempuh hidup baru.']);
    config(['services.turnstile.secret' => null]);
});

afterEach(fn () => RateLimiter::clear('x'));

it('stores an RSVP with a salted IP hash, never the raw address', function () {
    ($this->rsvp)()->assertCreated();

    $rsvp = Rsvp::query()->firstOrFail();
    expect($rsvp->name)->toBe('Budi')->and($rsvp->attendance->value)->toBe('yes')->and($rsvp->pax)->toBe(2)
        ->and($rsvp->invitation_id)->toBe($this->invitation->id)
        ->and($rsvp->ip_hash)->toHaveLength(64)->not->toContain('127.0.0.1');
});

it('stays cookie-free and needs no CSRF token', function () {
    $res = ($this->rsvp)()->assertCreated();

    expect($res->headers->has('Set-Cookie'))->toBeFalse();
});

it('counts a decline as one person whatever the number says', function () {
    ($this->rsvp)(['attendance' => 'no', 'pax' => 7])->assertCreated();

    expect(Rsvp::query()->firstOrFail()->pax)->toBe(1);
});

it('refuses invalid RSVPs with messages in Indonesian', function (array $bad, string $field) {
    $res = ($this->rsvp)($bad)->assertUnprocessable()->assertJsonValidationErrors($field);

    expect(Rsvp::query()->count())->toBe(0)->and(json_encode($res->json('errors')))->not->toContain(' field ');
})->with([
    'no name' => [['name' => ''], 'name'],
    'long name' => [['name' => str_repeat('a', 101)], 'name'],
    'bad attendance' => [['attendance' => 'perhaps'], 'attendance'],
    'too many guests' => [['pax' => 11], 'pax'],
    'zero guests' => [['pax' => 0], 'pax'],
    'long note' => [['note' => str_repeat('a', 301)], 'note'],
]);

it('pretends to accept a bot that fills the hidden field, and stores nothing', function () {
    ($this->rsvp)(['website' => 'http://spam.test'])->assertCreated();
    ($this->wish)(['website' => 'http://spam.test'])->assertCreated();

    expect(Rsvp::query()->count())->toBe(0)->and(GuestMessage::query()->count())->toBe(0);
});

it('throttles RSVPs from one address', function () {
    foreach (range(1, 10) as $_) {
        ($this->rsvp)()->assertCreated();
    }

    ($this->rsvp)()->assertTooManyRequests();
    expect(Rsvp::query()->count())->toBe(10);
});

it('answers 404 for a draft, an unknown slug and an invitation that switched RSVP off', function () {
    Invitation::factory()->wedding()->complete()->create(['slug' => 'masih-draf']);
    $this->invitation->update(['settings' => ['rsvp_enabled' => false]]);

    ($this->rsvp)([], 'masih-draf')->assertNotFound();
    ($this->rsvp)([], 'tidak-ada')->assertNotFound();
    ($this->rsvp)()->assertNotFound();
    ($this->wish)()->assertCreated(); // wishes are a separate switch
});

it('links an RSVP to the guest by token and keeps one answer per guest', function () {
    $guest = Guest::factory()->create(['invitation_id' => $this->invitation->id]);

    ($this->rsvp)(['g' => $guest->token, 'attendance' => 'maybe'])->assertCreated();
    ($this->rsvp)(['g' => $guest->token, 'attendance' => 'yes'])->assertCreated();

    expect(Rsvp::query()->where('guest_id', $guest->id)->count())->toBe(1)->and(Rsvp::query()->firstOrFail()->attendance->value)->toBe('yes');
});

it('ignores a token that belongs to another invitation', function () {
    $other = Guest::factory()->create();

    ($this->rsvp)(['g' => $other->token])->assertCreated();

    expect(Rsvp::query()->firstOrFail()->guest_id)->toBeNull();
});

it('stores a wish, cleaned of control characters and collapsed blank lines', function () {
    ($this->wish)(['message' => "Selamat\u{200B} ya\r\n\r\n\r\n\r\nSemoga bahagia\x07"])->assertCreated();

    expect(GuestMessage::query()->firstOrFail()->message)->toBe("Selamat ya\n\nSemoga bahagia");
});

it('keeps an HTML payload in a wish as plain text, and lists it as plain text', function () {
    $payload = '<script>alert(1)</script><img src=x onerror=alert(1)>';
    ($this->wish)(['name' => '<b>Eve</b>', 'message' => $payload])->assertCreated();

    $res = $this->getJson(($this->url)('wishes'))->assertOk();

    expect($res->json('data.0.message'))->toBe($payload)->and($res->json('data.0.name'))->toBe('<b>Eve</b>');
    // The JSON is data, not markup: the page script writes it with textContent (checked in the browser test).
    expect($res->headers->get('Content-Type'))->toContain('application/json');
});

it('refuses an empty wish and one that is only control characters', function () {
    ($this->wish)(['message' => ''])->assertUnprocessable();
    ($this->wish)(['message' => "\x07\x08"])->assertUnprocessable();

    expect(GuestMessage::query()->count())->toBe(0);
});

it('lists wishes newest first, ten at a time, with a cursor for the rest, and hides the hidden ones', function () {
    foreach (range(1, 12) as $i) {
        GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'name' => "Tamu {$i}", 'message' => "Ucapan {$i}"]);
    }
    GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Disembunyikan', 'message' => 'rahasia', 'is_visible' => false]);

    $first = $this->getJson(($this->url)('wishes'))->assertOk();
    expect($first->json('data'))->toHaveCount(10)->and($first->json('data.0.name'))->toBe('Tamu 12')->and($first->json('next'))->toBeString();

    $second = $this->getJson(($this->url)('wishes').'?cursor='.$first->json('next'))->assertOk();
    expect($second->json('data'))->toHaveCount(2)->and($second->json('next'))->toBeNull()
        ->and(json_encode([$first->json(), $second->json()]))->not->toContain('Disembunyikan');
});

it('never shows another invitation\'s wishes', function () {
    $other = Invitation::factory()->wedding()->complete()->published()->create(['slug' => 'lain-wxyz']);
    GuestMessage::factory()->create(['invitation_id' => $other->id, 'message' => 'milik orang lain']);

    expect($this->getJson(($this->url)('wishes'))->assertOk()->json('data'))->toBe([]);
});

it('throttles wishes', function () {
    foreach (range(1, 10) as $_) {
        ($this->wish)()->assertCreated();
    }

    ($this->wish)()->assertTooManyRequests();
});

it('asks Turnstile when it is configured and fails closed', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Http::fake(['challenges.cloudflare.com/*' => Http::sequence()->push(['success' => true])->push(['success' => false])->push(null, 500)]);

    ($this->rsvp)(['cf-turnstile-response' => 'good'])->assertCreated();
    ($this->rsvp)(['cf-turnstile-response' => 'bad'])->assertUnprocessable();
    ($this->wish)(['cf-turnstile-response' => 'error'])->assertUnprocessable();
    ($this->rsvp)()->assertUnprocessable(); // no token at all

    expect(Rsvp::query()->count())->toBe(1)->and(GuestMessage::query()->count())->toBe(0);
    Http::assertSentCount(3);
});

it('does not call Turnstile when it is not configured', function () {
    Http::fake();

    ($this->rsvp)()->assertCreated();

    Http::assertNothingSent();
});
