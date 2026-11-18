<?php

declare(strict_types=1);

use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Models\Invitation;

beforeEach(function () {
    $this->invitation = Invitation::factory()->published()->create(['slug' => 'raka-sinta-abcd']);
    $this->guest = Guest::factory()->for($this->invitation)->create();
    $this->beacon = fn (string $slug, string $g) => $this->post(route('public.guest.opened', ['slug' => $slug]), ['g' => $g]);
});

it('records the first open and answers 204', function () {
    ($this->beacon)('raka-sinta-abcd', $this->guest->token)->assertNoContent();
    expect($this->guest->fresh()->opened_at)->not->toBeNull();
});

it('keeps the first open time (idempotent)', function () {
    $this->travelTo(now()->setTime(10, 0));
    ($this->beacon)('raka-sinta-abcd', $this->guest->token);
    $first = $this->guest->fresh()->opened_at;
    $this->travel(2)->hours();
    ($this->beacon)('raka-sinta-abcd', $this->guest->token)->assertNoContent();
    expect($this->guest->fresh()->opened_at->equalTo($first))->toBeTrue();
});

it('answers 204 and changes nothing for unknown, malformed or mismatched tokens', function () {
    foreach (['', 'short', 'ABCDEFGHIJKL', str_repeat('a', 12), "'; DROP TABLE guests;--"] as $bad) {
        ($this->beacon)('raka-sinta-abcd', $bad)->assertNoContent();
    }
    $other = Invitation::factory()->published()->create(['slug' => 'lain-xyzw']);
    ($this->beacon)('lain-xyzw', $this->guest->token)->assertNoContent();   // token of another invitation
    expect($this->guest->fresh()->opened_at)->toBeNull();
});

it('ignores unpublished invitations', function () {
    $this->invitation->update(['status' => 'draft']);
    ($this->beacon)('raka-sinta-abcd', $this->guest->token)->assertNoContent();
    expect($this->guest->fresh()->opened_at)->toBeNull();
});

it('is rate limited', function () {
    $statuses = collect(range(1, 70))->map(fn () => ($this->beacon)('raka-sinta-abcd', 'abcdefghijkl')->status());
    expect($statuses->contains(429))->toBeTrue();
});
