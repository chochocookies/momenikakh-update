<?php

declare(strict_types=1);

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create();
    $this->message = GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Sari', 'message' => 'Selamat ya']);
    $this->url = fn (string $name, ?GuestMessage $m = null) => route("dashboard.responses.{$name}", $m ? [$this->invitation, $m] : [$this->invitation]);
});

it('summarises the RSVPs in a sentence and lists them with the people counted', function () {
    foreach ([['yes', 3], ['yes', 2], ['maybe', 1], ['no', 4]] as [$attendance, $pax]) {
        Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => $attendance, 'pax' => $pax]);
    }

    $html = $this->actingAs($this->owner)->get(($this->url)('index'))->assertOk()->getContent();

    expect($html)->toContain('4 konfirmasi. 2 hadir (5 orang), 1 masih ragu, 1 tidak hadir.');
});

it('says so when nothing has been sent yet', function () {
    GuestMessage::query()->delete();

    $this->actingAs($this->owner)->get(($this->url)('index'))->assertOk()->assertSee('belum ada konfirmasi kehadiran')->assertSee('Belum ada ucapan');
});

it('shows guest text escaped', function () {
    GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'name' => '<i>Eve</i>', 'message' => '<script>alert(1)</script>']);

    $html = $this->actingAs($this->owner)->get(($this->url)('index'))->getContent();

    expect($html)->not->toContain('<script>alert(1)')->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')->toContain('&lt;i&gt;Eve&lt;/i&gt;');
});

it('hides a wish, so the page stops listing it, and shows it again, with an audit row each time', function () {
    $this->actingAs($this->owner)->post(($this->url)('hide', $this->message))->assertRedirect()->assertSessionHas('toast');

    expect($this->message->fresh()->is_visible)->toBeFalse();
    $this->getJson(Surfaces::url('public', $this->invitation->slug.'/wishes'))->assertOk()->assertJsonCount(0, 'data');

    $this->actingAs($this->owner)->post(($this->url)('show', $this->message))->assertRedirect();

    expect($this->message->fresh()->is_visible)->toBeTrue()
        ->and(AuditLog::query()->where('invitation_id', $this->invitation->id)->pluck('action')->map(fn ($a) => $a->value ?? $a)->all())->toBe(['message.hidden', 'message.shown']);
    $this->getJson(Surfaces::url('public', $this->invitation->slug.'/wishes'))->assertJsonCount(1, 'data');
});

it('deletes a wish and records it without the text of the wish', function () {
    $this->actingAs($this->owner)->delete(($this->url)('destroy', $this->message))->assertRedirect();

    expect(GuestMessage::query()->count())->toBe(0);
    $row = AuditLog::query()->where('invitation_id', $this->invitation->id)->firstOrFail();
    expect(json_encode($row->toArray()))->toContain('message.deleted')->not->toContain('Selamat ya')->not->toContain('Sari');
});

it('keeps other people out of the page and the moderation, and visitors at the login page', function () {
    $this->get(($this->url)('index'))->assertRedirect(route('login'));
    $this->actingAs($this->stranger)->get(($this->url)('index'))->assertForbidden();
    $this->actingAs($this->stranger)->post(($this->url)('hide', $this->message))->assertForbidden();
    $this->actingAs($this->stranger)->delete(($this->url)('destroy', $this->message))->assertForbidden();

    expect($this->message->fresh()->is_visible)->toBeTrue()->and(GuestMessage::query()->count())->toBe(1);
});

it('cannot moderate a wish through another invitation of the same owner', function () {
    $other = Invitation::factory()->for($this->owner)->wedding()->complete()->create();

    $this->actingAs($this->owner)->post(route('dashboard.responses.hide', [$other, $this->message]))->assertNotFound();

    expect($this->message->fresh()->is_visible)->toBeTrue();
});
