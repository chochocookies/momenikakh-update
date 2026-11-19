<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvalidTransition;
use App\Domain\Invitation\Services\InvitationLifecycle;
use App\Domain\Invitation\Services\PublishRequiresPlan;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->lifecycle = app(InvitationLifecycle::class);
    $this->freeze = now()->setDate(2027, 1, 10)->setTime(12, 0);
    $this->travelTo($this->freeze);
    $this->seed(PlanSeeder::class);
    /** Gives an invitation the paid Paket Template (DEC-01: Rp 50.000, once, no expiry). */
    $this->pay = fn (Invitation $i) => Subscription::factory()->forInvitation($i)->create(['plan_id' => Plan::query()->where('key', 'paket-template')->value('id'), 'ends_at' => null]);
});

it('describes the allowed transitions', function () {
    $s = InvitationStatus::class;
    expect($s::Draft->canTransitionTo($s::Published))->toBeTrue();
    expect($s::Draft->canTransitionTo($s::Expired))->toBeFalse();
    expect($s::Published->canTransitionTo($s::Draft))->toBeTrue();
    expect($s::Expired->canTransitionTo($s::Published))->toBeTrue();      // renewal
    expect($s::Archived->canTransitionTo($s::Published))->toBeFalse();    // must restore to draft first
    expect($s::Archived->canTransitionTo($s::Draft))->toBeTrue();
});

it('publishes a draft and records when', function () {
    $i = Invitation::factory()->create();
    ($this->pay)($i);
    $this->lifecycle->publish($i);
    expect($i->fresh()->status)->toBe(InvitationStatus::Published);
    expect($i->fresh()->published_at->equalTo($this->freeze))->toBeTrue();
});

it('keeps a free sample unpublished until the Paket Template is bought (DEC-01)', function () {
    $sample = Invitation::factory()->create();

    expect(fn () => $this->lifecycle->publish($sample))->toThrow(PublishRequiresPlan::class);
    expect($sample->fresh()->status)->toBe(InvitationStatus::Draft)->and($sample->fresh()->published_at)->toBeNull();

    ($this->pay)($sample);
    $this->lifecycle->publish($sample);
    expect($sample->fresh()->status)->toBe(InvitationStatus::Published);
});

it('does not let the plan of one invitation publish another', function () {
    $paid = Invitation::factory()->create();
    $other = Invitation::factory()->for($paid->user)->create();
    ($this->pay)($paid);

    expect(fn () => $this->lifecycle->publish($other))->toThrow(PublishRequiresPlan::class);
});

it('refuses illegal transitions', function () {
    $draft = Invitation::factory()->create();
    expect(fn () => $this->lifecycle->expire($draft))->toThrow(InvalidTransition::class);
    $archived = Invitation::factory()->archived()->create();
    expect(fn () => $this->lifecycle->publish($archived))->toThrow(InvalidTransition::class);
    expect($archived->fresh()->status)->toBe(InvitationStatus::Archived); // unchanged
});

it('supports unpublish, archive and restore', function () {
    $i = Invitation::factory()->published()->create();
    $this->lifecycle->unpublish($i);
    expect($i->fresh()->status)->toBe(InvitationStatus::Draft);
    $this->lifecycle->archive($i);
    expect($i->fresh()->status)->toBe(InvitationStatus::Archived);
    $this->lifecycle->restore($i);
    expect($i->fresh()->status)->toBe(InvitationStatus::Draft);
});

it('sets expiry from the active subscription or the plan duration', function () {
    $i = Invitation::factory()->create();
    Subscription::factory()->forInvitation($i)->create(['ends_at' => now()->addDays(10), 'plan_id' => Plan::factory()->withFeatures(['publish' => true])]);
    $this->lifecycle->publish($i);
    expect($i->fresh()->expires_at->equalTo(now()->addDays(10)))->toBeTrue();
});

it('has no expiry when the plan has none', function () {
    $i = Invitation::factory()->create();
    ($this->pay)($i);
    $this->lifecycle->publish($i); // Paket Template has no duration (DEC-01)
    expect($i->fresh()->expires_at)->toBeNull();
});

it('expires only published invitations that are due', function () {
    $due = Invitation::factory()->published()->create(['expires_at' => now()->subMinute()]);
    $dueExact = Invitation::factory()->published()->create(['expires_at' => now()]);
    $future = Invitation::factory()->published()->create(['expires_at' => now()->addDay()]);
    $never = Invitation::factory()->published()->create(['expires_at' => null]);
    $draftPast = Invitation::factory()->create(['expires_at' => now()->subDay()]);

    expect($this->lifecycle->expireDue())->toBe(2);

    expect($due->fresh()->status)->toBe(InvitationStatus::Expired);
    expect($dueExact->fresh()->status)->toBe(InvitationStatus::Expired);
    expect($future->fresh()->status)->toBe(InvitationStatus::Published);
    expect($never->fresh()->status)->toBe(InvitationStatus::Published);
    expect($draftPast->fresh()->status)->toBe(InvitationStatus::Draft);
    expect($this->lifecycle->expireDue())->toBe(0); // idempotent
});

it('lets an expired invitation be renewed', function () {
    $i = Invitation::factory()->expired()->create();
    ($this->pay)($i);
    $this->lifecycle->publish($i);
    expect($i->fresh()->status)->toBe(InvitationStatus::Published);
});

it('runs the expiry command and schedules it hourly', function () {
    Invitation::factory()->published()->create(['expires_at' => now()->subHour()]);
    $this->artisan('invitations:expire')->expectsOutputToContain('Expired 1')->assertSuccessful();
    $this->artisan('schedule:list')->expectsOutputToContain('invitations:expire')->assertSuccessful();
});

it('only shows published invitations as publicly visible', function () {
    foreach (InvitationStatus::cases() as $status) {
        expect($status->isPubliclyVisible())->toBe($status === InvitationStatus::Published);
    }
});
