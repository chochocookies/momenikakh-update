<?php

declare(strict_types=1);

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationReadiness;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(PlanSeeder::class);
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->complete = fn (array $attrs = []) => Invitation::factory()->for($this->owner)->wedding()->complete()->create($attrs);
    $this->pay = fn (Invitation $i) => Subscription::factory()->forInvitation($i)->create(['plan_id' => Plan::query()->where('key', 'paket-template')->value('id'), 'ends_at' => null]);
    $this->publishUrl = fn (Invitation $i) => route('dashboard.invitations.publish', $i);
    $this->unpublishUrl = fn (Invitation $i) => route('dashboard.invitations.unpublish', $i);
});

it('says an invitation with all data is ready, and lists the step that is not when something is missing', function () {
    $invitation = ($this->complete)();
    $readiness = app(InvitationReadiness::class);

    expect($readiness->check($invitation))->toBe(['ready' => true, 'steps' => ['basics' => null, 'people' => null, 'events' => null, 'story' => null, 'gifts' => null]]);

    $invitation->events()->delete();
    $check = $readiness->check($invitation);

    expect($check['ready'])->toBeFalse()->and($check['steps']['events'])->toBeString()->and($check['steps']['people'])->toBeNull();
});

it('is not ready without a template that fits the event type', function () {
    $invitation = ($this->complete)();
    $invitation->update(['template_slug' => null]); // the sample data sets a template, so remove it after

    expect(app(InvitationReadiness::class)->check($invitation)['steps']['basics'])->toBe('Pilih template yang cocok dengan jenis acara.');
});

it('publishes a complete invitation that has the Paket Template, records it, and makes the page open', function () {
    $invitation = ($this->complete)();
    ($this->pay)($invitation);

    $this->actingAs($this->owner)->post(($this->publishUrl)($invitation))->assertRedirect(route('dashboard.invitations.show', $invitation))->assertSessionHas('toast');

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Published)
        ->and(AuditLog::query()->where('action', 'invitation.published')->where('invitation_id', $invitation->id)->exists())->toBeTrue();
    $this->get(Surfaces::url('public', '/'.$invitation->slug))->assertOk();
});

it('does not publish a free sample and says why', function () {
    $invitation = ($this->complete)();

    $this->actingAs($this->owner)->post(($this->publishUrl)($invitation))->assertSessionHas('alert');

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Draft);
});

it('does not publish when data is missing, even with the Paket Template', function () {
    $invitation = ($this->complete)();
    ($this->pay)($invitation);
    $invitation->events()->delete();

    $this->actingAs($this->owner)->post(($this->publishUrl)($invitation))->assertSessionHas('alert', 'Lengkapi datanya dulu sebelum menerbitkan.');

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Draft);
    $this->get(Surfaces::url('public', '/'.$invitation->slug))->assertNotFound();
});

it('takes a published invitation back to draft and closes the link', function () {
    $invitation = ($this->complete)();
    ($this->pay)($invitation);
    $this->actingAs($this->owner)->post(($this->publishUrl)($invitation));

    $this->actingAs($this->owner)->post(($this->unpublishUrl)($invitation))->assertSessionHas('toast');

    expect($invitation->fresh()->status)->toBe(InvitationStatus::Draft);
    $this->get(Surfaces::url('public', '/'.$invitation->slug))->assertNotFound();
});

it('refuses to unpublish a draft', function () {
    $invitation = ($this->complete)();

    $this->actingAs($this->owner)->post(($this->unpublishUrl)($invitation))->assertSessionHas('alert');
});

it('keeps other people out of publish and unpublish, and visitors at the login page', function () {
    $invitation = ($this->complete)();
    ($this->pay)($invitation);

    $this->post(($this->publishUrl)($invitation))->assertRedirect(route('login'));
    $this->actingAs($this->stranger)->post(($this->publishUrl)($invitation))->assertForbidden();
    $this->actingAs($this->stranger)->post(($this->unpublishUrl)($invitation))->assertForbidden();
    expect($invitation->fresh()->status)->toBe(InvitationStatus::Draft);
});

it('shows the steps on the invitation page, with the button that fits the state', function () {
    $invitation = ($this->complete)();
    $show = fn () => $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertOk();

    // A sample: price prompt, no publish button.
    $html = $show()->getContent();
    expect($html)->toContain('1. Isi data')->toContain('Rp')->not->toContain(($this->publishUrl)($invitation));

    // Paid and complete: publish button.
    ($this->pay)($invitation);
    expect($show()->getContent())->toContain(($this->publishUrl)($invitation));

    // Published: link, copy, WhatsApp share and the way back to draft.
    $this->actingAs($this->owner)->post(($this->publishUrl)($invitation));
    $res = $show();
    $xp = DesignSystem::xpath($res->getContent());
    expect($res->getContent())->toContain($invitation->slug)->toContain(($this->unpublishUrl)($invitation))
        ->and($xp->query('//a[contains(@href,"https://wa.me/")][@target="_blank"]')->length)->toBe(1)
        ->and($xp->query('//h1')->length)->toBe(1);
});

it('lists what is missing on the invitation page', function () {
    $invitation = ($this->complete)();
    $invitation->events()->delete();

    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertOk()->assertSee('Lengkapi bagian ini sebelum menerbitkan')->assertSee('Acara dan lokasi:');
});
