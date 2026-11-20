<?php

declare(strict_types=1);

use App\Domain\Account\Onboarding;
use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Invitation\Support\SamplePayload;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->owner = User::factory()->create();
    $this->steps = fn () => collect(app(Onboarding::class)->steps($this->owner->fresh()))->pluck('done', 'label')->all();
});

it('shows a new owner the list, with the first step to do and no separate empty state', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard'))->assertOk();

    expect($res->getContent())->toContain('Mulai dari sini')->toContain('1. Buat undangan pertamamu')->not->toContain('Belum ada undangan');
    expect(($this->steps)())->toBe(['Buat undangan pertamamu' => false]);
});

it('checks each step from what the owner really did', function () {
    $invitation = Invitation::factory()->for($this->owner)->wedding()->create();

    expect(($this->steps)())->toBe(['Buat undangan pertamamu' => true, 'Isi datanya' => false, 'Tambahkan tamu' => false, 'Terbitkan dan bagikan' => false]);

    app(InvitationWriter::class)->applyAll($invitation, SamplePayload::for($invitation->event_type));
    expect(($this->steps)()['Isi datanya'])->toBeTrue();

    Guest::factory()->for($invitation)->create();
    expect(($this->steps)()['Tambahkan tamu'])->toBeTrue();
});

it('closes for good when the owner closes it, and only for that owner', function () {
    $other = User::factory()->create();

    $this->actingAs($this->owner)->post(route('dashboard.onboarding.dismiss'))->assertRedirect(route('dashboard'));

    expect($this->owner->fresh()->onboarding_dismissed_at)->not->toBeNull()->and($other->onboarding_dismissed_at)->toBeNull();
    $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->assertDontSee('Mulai dari sini');
    $this->actingAs($other)->get(route('dashboard'))->assertOk()->assertSee('Mulai dari sini');
});

it('shows the usual empty state after it was closed', function () {
    $this->actingAs($this->owner)->post(route('dashboard.onboarding.dismiss'));

    $this->actingAs($this->owner)->get(route('dashboard'))->assertSee('Belum ada undangan');
});

it('goes away by itself once the owner has a published invitation', function () {
    Invitation::factory()->for($this->owner)->wedding()->published()->create();

    expect(app(Onboarding::class)->steps($this->owner))->toBeNull();
    $this->actingAs($this->owner)->get(route('dashboard'))->assertDontSee('Mulai dari sini');
});

it('needs a signed-in user to close the list', function () {
    $this->post(route('dashboard.onboarding.dismiss'))->assertRedirect(route('login'));
});
