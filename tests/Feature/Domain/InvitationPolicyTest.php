<?php

declare(strict_types=1);

use App\Domain\Account\Enums\UserRole;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

beforeEach(function () {
    $this->alice = User::factory()->create();
    $this->bob = User::factory()->create();
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->aliceInvitation = Invitation::factory()->for($this->alice)->create();
    $this->bobInvitation = Invitation::factory()->for($this->bob)->create();
});

it('lets owners manage their own invitation', function () {
    foreach (['view', 'update', 'delete', 'publish', 'manageGuests', 'moderate'] as $ability) {
        expect($this->alice->can($ability, $this->aliceInvitation))->toBeTrue($ability);
    }
});

it('denies every ability on someone else\'s invitation (IDOR)', function () {
    foreach (['view', 'update', 'delete', 'publish', 'manageGuests', 'moderate'] as $ability) {
        expect($this->alice->can($ability, $this->bobInvitation))->toBeFalse($ability);
        expect($this->bob->can($ability, $this->aliceInvitation))->toBeFalse($ability);
    }
});

it('lets admins do everything', function () {
    foreach (['view', 'update', 'delete', 'publish', 'manageGuests', 'moderate'] as $ability) {
        expect($this->admin->can($ability, $this->aliceInvitation))->toBeTrue($ability);
    }
});

it('scopes queries to the owner with ownedBy', function () {
    Invitation::factory()->for($this->alice)->count(2)->create();
    $mine = Invitation::query()->ownedBy($this->alice)->get();
    expect($mine)->toHaveCount(3)->and($mine->pluck('user_id')->unique()->all())->toBe([$this->alice->id]);
    expect(Invitation::query()->ownedBy($this->alice->id)->count())->toBe(3);
    expect(Invitation::query()->ownedBy($this->bob)->pluck('id')->all())->toBe([$this->bobInvitation->id]);
});

it('protects guests, rsvps and messages through the invitation owner', function (string $model) {
    $record = $model::factory()->for($this->bobInvitation)->create();
    foreach (['view', 'update', 'delete'] as $ability) {
        expect($this->alice->can($ability, $record))->toBeFalse("{$model} {$ability}");
        expect($this->bob->can($ability, $record))->toBeTrue("{$model} {$ability}");
        expect($this->admin->can($ability, $record))->toBeTrue("{$model} {$ability}");
    }
})->with([Guest::class, Rsvp::class, GuestMessage::class]);

it('denies guests who are not logged in', function () {
    $this->get('/__no-route')->assertNotFound(); // sanity: app boots
    expect(Gate::forUser(null)->denies('view', $this->aliceInvitation))->toBeTrue();
});

it('detects ownership without trusting mass assignment', function () {
    expect($this->aliceInvitation->isOwnedBy($this->alice))->toBeTrue();
    expect($this->aliceInvitation->isOwnedBy($this->bob))->toBeFalse();
});
