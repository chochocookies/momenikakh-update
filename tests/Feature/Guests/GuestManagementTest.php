<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->create(['slug' => 'raka-sinta-abcd', 'title' => 'Raka & Sinta']);
});

it('lists guests with a personal link and a WhatsApp link per guest', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['name' => 'Ni Luh Ayu & Keluarga', 'phone' => '6281234567890']);

    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.index', $this->invitation))->assertOk();

    $res->assertSee('Ni Luh Ayu &amp; Keluarga', false);                      // names are escaped
    $res->assertSee('raka-sinta-abcd?to=Ni%20Luh%20Ayu%20%26%20Keluarga&amp;g='.$guest->token, false);
    $res->assertSee('https://wa.me/6281234567890?text=', false);
});

it('builds links that decode back to the exact guest name', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['name' => "Dr. O'Brien & Ani/Budi <b>"]);
    $url = app(GuestLinkBuilder::class)->forGuest($this->invitation->slug, $guest->name, $guest->token);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    expect($q['to'])->toBe("Dr. O'Brien & Ani/Budi <b>")->and($q['g'])->toBe($guest->token);
});

it('uses a custom share message from the invitation settings', function () {
    $this->invitation->update(['settings' => ['share_message' => 'Salam {nama}! Undangan: {tautan}']]);
    Guest::factory()->for($this->invitation)->create(['name' => 'Siti', 'phone' => '6281200000001']);
    $this->actingAs($this->owner)->get(route('dashboard.guests.index', $this->invitation))
        ->assertSee('text=Salam%20Siti%21', false);
});

it('adds a guest and normalises the phone number', function () {
    $this->actingAs($this->owner)
        ->post(route('dashboard.guests.store', $this->invitation), ['name' => '  Budi   Santoso ', 'group' => 'Keluarga', 'phone' => '0812-3456-7890'])
        ->assertRedirect();

    $guest = $this->invitation->guests()->first();
    expect($guest->name)->toBe('Budi Santoso')->and($guest->phone)->toBe('6281234567890')->and($guest->token)->toMatch('/^[a-z0-9]{12}$/');
});

it('rejects an invalid phone or a missing name', function () {
    $this->actingAs($this->owner)
        ->post(route('dashboard.guests.store', $this->invitation), ['name' => 'Budi', 'phone' => 'abc'])
        ->assertSessionHasErrors('phone');
    $this->actingAs($this->owner)
        ->post(route('dashboard.guests.store', $this->invitation), ['name' => ''])
        ->assertSessionHasErrors('name');
    expect($this->invitation->guests()->count())->toBe(0);
});

it('does not let control or bidi characters into stored names', function () {
    $this->actingAs($this->owner)
        ->post(route('dashboard.guests.store', $this->invitation), ['name' => "Ahmad\u{202E}evil\u{0000}"]);
    expect($this->invitation->guests()->first()->name)->not->toMatch('/[\x{202E}\x{0000}]/u');
});

it('enforces the guest limit of the plan', function () {
    $plan = Plan::factory()->withLimits(['max_guests' => 2])->create();
    Subscription::factory()->for($this->owner)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);
    Guest::factory()->count(2)->for($this->invitation)->create();

    $this->actingAs($this->owner)
        ->post(route('dashboard.guests.store', $this->invitation), ['name' => 'Tamu ketiga'])
        ->assertSessionHasErrors('name');
    expect($this->invitation->guests()->count())->toBe(2);
});

it('forbids every guest action on someone else\'s invitation (IDOR)', function () {
    $guest = Guest::factory()->for($this->invitation)->create();
    $as = fn () => $this->actingAs($this->stranger);

    $as()->get(route('dashboard.guests.index', $this->invitation))->assertForbidden();
    $as()->post(route('dashboard.guests.store', $this->invitation), ['name' => 'X'])->assertForbidden();
    $as()->delete(route('dashboard.guests.destroy', [$this->invitation, $guest]))->assertForbidden();
    $as()->post(route('dashboard.guests.token', [$this->invitation, $guest]))->assertForbidden();
    $as()->get(route('dashboard.guests.template', $this->invitation))->assertForbidden();
    $as()->post(route('dashboard.guests.import.preview', $this->invitation))->assertForbidden();
    $as()->get(route('dashboard.guests.export.rsvps', $this->invitation))->assertForbidden();
    expect(Guest::query()->whereKey($guest->id)->exists())->toBeTrue();
});

it('requires login', function () {
    $this->get(route('dashboard.guests.index', $this->invitation))->assertRedirect();
});

it('does not touch a guest that belongs to another invitation (scoped bindings)', function () {
    $other = Invitation::factory()->for($this->owner)->create();
    $foreign = Guest::factory()->for($other)->create();
    $this->actingAs($this->owner)
        ->delete(route('dashboard.guests.destroy', [$this->invitation, $foreign]))
        ->assertNotFound();
    expect(Guest::query()->whereKey($foreign->id)->exists())->toBeTrue();
});

it('deletes an own guest', function () {
    $guest = Guest::factory()->for($this->invitation)->create();
    $this->actingAs($this->owner)->delete(route('dashboard.guests.destroy', [$this->invitation, $guest]))->assertRedirect();
    expect(Guest::query()->whereKey($guest->id)->exists())->toBeFalse();
});

it('regenerates a token and clears the opened status', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['name' => 'Budi']);
    $guest->forceFill(['opened_at' => now()])->save();
    $old = $guest->token;

    $this->actingAs($this->owner)->post(route('dashboard.guests.token', [$this->invitation, $guest]))->assertRedirect();

    $guest->refresh();
    expect($guest->token)->not->toBe($old)->and($guest->opened_at)->toBeNull();
});

it('searches by name without treating % and _ as wildcards', function () {
    Guest::factory()->for($this->invitation)->create(['name' => 'Budi 100% Hadir']);
    Guest::factory()->for($this->invitation)->create(['name' => 'Siti']);
    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.index', [$this->invitation, 'q' => '%']))->assertOk();
    $res->assertSee('Budi 100% Hadir')->assertDontSee('Siti');
});
