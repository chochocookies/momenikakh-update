<?php

declare(strict_types=1);

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

beforeEach(function () {
    $this->ent = app(Entitlements::class);
    $this->user = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->user)->create();
});

it('falls back to the free plan when there is no subscription', function () {
    expect($this->ent->hasActivePlan($this->invitation))->toBeFalse();
    expect($this->ent->hasActivePlan($this->user))->toBeFalse();
    expect($this->ent->planFor($this->invitation)->isFree())->toBeTrue();
    expect($this->ent->can($this->invitation, 'rsvp'))->toBeTrue();            // free features come from config
    expect($this->ent->can($this->invitation, 'premium_templates'))->toBeFalse();
});

it('uses the seeded free plan row when it exists', function () {
    Plan::factory()->free()->withFeatures(['rsvp' => false])->create();
    expect($this->ent->can($this->invitation, 'rsvp'))->toBeFalse();
});

it('grants abilities of an active premium subscription', function () {
    $plan = Plan::factory()->premium()->withFeatures(['premium_templates' => true, 'remove_brand' => false])->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);

    expect($this->ent->hasActivePlan($this->invitation))->toBeTrue();
    expect($this->ent->can($this->invitation, 'premium_templates'))->toBeTrue();
    expect($this->ent->can($this->invitation, 'remove_brand'))->toBeFalse();
});

it('answers per plan, per invitation', function () {
    $other = Invitation::factory()->for($this->user)->create();
    $plan = Plan::factory()->withFeatures(['premium_templates' => true])->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);

    expect($this->ent->can($this->invitation, 'premium_templates'))->toBeTrue();
    expect($this->ent->can($other, 'premium_templates'))->toBeFalse();   // plan is attached to one invitation
});

it('applies an account-level subscription to all of the owner\'s invitations', function () {
    $other = Invitation::factory()->for($this->user)->create();
    $plan = Plan::factory()->withFeatures(['premium_templates' => true])->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'invitation_id' => null]);

    expect($this->ent->can($this->invitation, 'premium_templates'))->toBeTrue();
    expect($this->ent->can($other, 'premium_templates'))->toBeTrue();
    expect($this->ent->hasActivePlan($this->user))->toBeTrue();
});

it('does not leak a plan to another user', function () {
    $stranger = Invitation::factory()->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => Plan::factory()]);
    expect($this->ent->hasActivePlan($stranger))->toBeFalse();
});

it('ignores expired, pending and cancelled subscriptions', function () {
    $plan = Plan::factory()->create();
    Subscription::factory()->for($this->user)->expired()->create(['plan_id' => $plan->id]);
    Subscription::factory()->for($this->user)->pending()->create(['plan_id' => $plan->id]);
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'status' => 'cancelled']);
    expect($this->ent->hasActivePlan($this->invitation))->toBeFalse();
});

it('respects the time of the check', function () {
    $plan = Plan::factory()->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(5)]);
    expect($this->ent->hasActivePlan($this->invitation))->toBeFalse();
    expect($this->ent->hasActivePlan($this->invitation, now()->addDays(3)))->toBeTrue();
    expect($this->ent->hasActivePlan($this->invitation, now()->addDays(6)))->toBeFalse();
});

it('treats a lifetime subscription as always active', function () {
    Subscription::factory()->for($this->user)->lifetime()->create(['plan_id' => Plan::factory()]);
    expect($this->ent->hasActivePlan($this->invitation, now()->addYears(10)))->toBeTrue();
});

it('prefers the invitation-level plan over the account-level one', function () {
    $basic = Plan::factory()->withFeatures(['premium_templates' => false])->create();
    $gold = Plan::factory()->withFeatures(['premium_templates' => true])->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $basic->id, 'invitation_id' => null, 'ends_at' => now()->addYear()]);
    Subscription::factory()->for($this->user)->create(['plan_id' => $gold->id, 'invitation_id' => $this->invitation->id, 'ends_at' => now()->addDays(5)]);
    expect($this->ent->can($this->invitation, 'premium_templates'))->toBeTrue();
});

it('reads numeric limits and treats null as undecided', function () {
    $plan = Plan::factory()->withLimits(['max_photos' => 12])->create();
    Subscription::factory()->for($this->user)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);
    expect($this->ent->limit($this->invitation, 'max_photos'))->toBe(12);
    expect($this->ent->limit($this->invitation, 'max_guests'))->toBeNull();
});

it('throws on unknown abilities and limits so typos cannot silently deny or allow', function () {
    expect(fn () => $this->ent->can($this->invitation, 'premum_templates'))->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->ent->limit($this->invitation, 'max_photoz'))->toThrow(InvalidArgumentException::class);
});

it('is the only place that reads plan features', function () {
    $offenders = [];
    foreach (File::allFiles(base_path('app')) as $file) {
        $path = str_replace(base_path().'/', '', $file->getPathname());
        if (str_contains($path, 'Domain/Billing/') || str_contains($path, 'Factories')) {
            continue;
        }
        if (preg_match('/->features\b|->limits\b|->tier\b/', $file->getContents())) {
            $offenders[] = $path;
        }
    }
    expect($offenders)->toBeEmpty();
});
