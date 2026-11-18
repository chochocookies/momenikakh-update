<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventKind;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;

it('defines exactly the six event types', function () {
    expect(EventType::values())->toEqual(['wedding', 'walimatul_urs', 'engagement', 'aqiqah', 'khitanan', 'birthday']);
    expect(app(EventTypeRegistry::class)->keys())->toEqualCanonicalizing(EventType::values());
});

it('builds a complete definition for every event type', function (EventType $type) {
    $def = app(EventTypeRegistry::class)->for($type);

    expect($def->label)->not->toBe('')
        ->and($def->roles)->not->toBeEmpty()
        ->and($def->requiredRoles())->not->toBeEmpty()
        ->and($def->events['kinds'])->not->toBeEmpty()
        ->and($def->events['min'])->toBeGreaterThanOrEqual(1)
        ->and($def->sections)->toContain('cover', 'events', 'closing')
        ->and($def->content)->toHaveKey('opening');
    foreach (EventTypeDefinition::STEPS as $step) {
        expect($def->rules($step))->not->toBeEmpty("rules for {$step}");
    }
})->with(fn () => EventType::cases());

it('uses only known role and event kind values', function () {
    $roles = array_map(fn ($c) => $c->value, PersonRole::cases());
    $kinds = array_map(fn ($c) => $c->value, EventKind::cases());
    foreach (app(EventTypeRegistry::class)->all() as $def) {
        expect(array_diff($def->roleKeys(), $roles))->toBeEmpty("{$def->key()} has unknown roles");
        expect(array_diff($def->kinds(), $kinds))->toBeEmpty("{$def->key()} has unknown event kinds");
    }
});

it('fails loudly when config is missing an event type or has an unknown one', function () {
    $config = config('event-types');
    $missing = $config;
    unset($missing['aqiqah']);
    expect(fn () => new EventTypeRegistry($missing))->toThrow(LogicException::class);
    expect(fn () => new EventTypeRegistry($config + ['mystery' => []]))->toThrow(LogicException::class);
});

it('rejects an unknown editor step', function () {
    expect(fn () => app(EventTypeRegistry::class)->for('wedding')->rules('nope'))->toThrow(InvalidArgumentException::class);
});

it('enforces role counts per event type', function () {
    $validator = fn (EventType $t, array $people) => Validator::make(['people' => $people], app(EventTypeRegistry::class)->for($t)->rules('people'));

    expect($validator(EventType::Wedding, [['role' => 'groom', 'name' => 'A']])->fails())->toBeTrue();  // bride missing
    expect($validator(EventType::Wedding, [['role' => 'groom', 'name' => 'A'], ['role' => 'groom', 'name' => 'B']])->fails())->toBeTrue();
    expect($validator(EventType::Wedding, [['role' => 'groom', 'name' => 'A'], ['role' => 'bride', 'name' => 'B']])->passes())->toBeTrue();
    expect($validator(EventType::Aqiqah, [['role' => 'groom', 'name' => 'A']])->fails())->toBeTrue();   // role not allowed
    expect($validator(EventType::Aqiqah, [['role' => 'baby', 'name' => 'A'], ['role' => 'parent', 'name' => 'B']])->passes())->toBeTrue();
});

it('only allows https map links and rejects other schemes', function () {
    $rules = app(EventTypeRegistry::class)->for('wedding')->rules('events');
    $row = fn (string $url) => ['events' => [[
        'kind' => 'akad', 'starts_at' => '2027-03-14 09:00', 'venue_name' => 'X', 'address' => 'Y', 'maps_url' => $url,
    ]]];
    expect(Validator::make($row('https://maps.google.com/?q=1,2'), $rules)->passes())->toBeTrue();
    expect(Validator::make($row('javascript:alert(1)'), $rules)->fails())->toBeTrue();
    expect(Validator::make($row('http://insecure.example'), $rules)->fails())->toBeTrue();
});

it('requires an end time after the start time', function () {
    $rules = app(EventTypeRegistry::class)->for('wedding')->rules('events');
    $payload = ['events' => [[
        'kind' => 'akad', 'starts_at' => '2027-03-14 09:00', 'ends_at' => '2027-03-14 08:00', 'venue_name' => 'X', 'address' => 'Y',
    ]]];
    expect(Validator::make($payload, $rules)->fails())->toBeTrue();
});

it('blocks the story step for event types without the story feature', function () {
    $rules = app(EventTypeRegistry::class)->for('aqiqah')->rules('story');
    expect(Validator::make(['story' => [['title' => 'x', 'body' => 'y']]], $rules)->fails())->toBeTrue();
    expect(Validator::make(['story' => []], $rules)->passes())->toBeTrue();
});

it('requires bank details only for bank and e-wallet gifts', function () {
    $rules = app(EventTypeRegistry::class)->for('wedding')->rules('gifts');
    expect(Validator::make(['gifts' => [['type' => 'bank']]], $rules)->fails())->toBeTrue();
    expect(Validator::make(['gifts' => [['type' => 'address']]], $rules)->fails())->toBeTrue();
    expect(Validator::make(['gifts' => [['type' => 'address', 'address_text' => 'Jl. Contoh']]], $rules)->passes())->toBeTrue();
    expect(Validator::make(['gifts' => [['type' => 'bank', 'provider' => 'B', 'holder_name' => 'H', 'number' => '12']]], $rules)->fails())->toBeTrue();
});
