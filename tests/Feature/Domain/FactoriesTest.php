<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Support\SamplePayload;


it('creates a draft invitation with casts applied', function () {
    $i = Invitation::factory()->create();
    expect($i->event_type)->toBe(EventType::Wedding)
        ->and($i->status->value)->toBe('draft')
        ->and($i->content)->toBe([])
        ->and($i->user_id)->not->toBeNull();
});

it('gives every event type a factory state that produces valid data', function (EventType $type) {
    $invitation = Invitation::factory()->forType($type)->complete()->create();
    $registry = app(EventTypeRegistry::class);
    $def = $registry->for($type);
    $data = InvitationData::fromModel($invitation->fresh());

    // Every required role is present and counts are within bounds.
    foreach ($def->roles as $role => $rule) {
        $n = count(array_filter($data->people, fn ($p) => $p->role->value === $role));
        expect($n)->toBeGreaterThanOrEqual($rule['min'])->toBeLessThanOrEqual($rule['max']);
    }
    expect(count($data->events))->toBeGreaterThanOrEqual($def->events['min']);
    foreach ($data->events as $e) {
        expect($def->allowsKind($e->kind->value))->toBeTrue();
    }
    expect($data->story === [] || $def->hasFeature('story'))->toBeTrue();
    expect($data->gifts === [] || $def->hasFeature('gifts'))->toBeTrue();

    // The sample payload itself passes the real validation rules for every step.
    foreach (SamplePayload::for($type) as $step => $payload) {
        $v = Validator::make($payload, $def->rules($step));
        expect($v->errors()->all())->toBeEmpty("step {$step} of {$type->value}");
    }
})->with(fn () => EventType::cases());

it('has named factory states for each event type', function () {
    expect(Invitation::factory()->wedding()->make()->event_type)->toBe(EventType::Wedding);
    expect(Invitation::factory()->walimatulUrs()->make()->event_type)->toBe(EventType::WalimatulUrs);
    expect(Invitation::factory()->engagement()->make()->event_type)->toBe(EventType::Engagement);
    expect(Invitation::factory()->aqiqah()->make()->event_type)->toBe(EventType::Aqiqah);
    expect(Invitation::factory()->khitanan()->make()->event_type)->toBe(EventType::Khitanan);
    expect(Invitation::factory()->birthday()->make()->event_type)->toBe(EventType::Birthday);
});

it('does not allow mass assignment of the owner or token', function () {
    $user = App\Models\User::factory()->create();
    $i = new Invitation(['user_id' => 999, 'title' => 'X', 'slug' => 'x-abcd', 'event_type' => 'wedding']);
    expect($i->user_id)->toBeNull();
    $guest = new App\Domain\Guest\Models\Guest(['name' => 'A', 'token' => 'hacked']);
    expect($guest->token)->toBeNull();
    $saved = $user->invitations()->create(['title' => 'X', 'slug' => 'x-abcd', 'event_type' => 'wedding']);
    expect($saved->user_id)->toBe($user->id);
});

it('creates a unique token for every guest', function () {
    $invitation = Invitation::factory()->create();
    $guests = App\Domain\Guest\Models\Guest::factory()->count(20)->for($invitation)->create();
    expect($guests->pluck('token')->unique())->toHaveCount(20);
    expect($guests->first()->token)->toMatch('/^[a-z0-9]{12}$/');
});
