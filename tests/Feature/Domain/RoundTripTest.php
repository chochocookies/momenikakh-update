<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Invitation\Support\SamplePayload;
use App\Http\Requests\Editor\UpdateBasicsRequest;
use App\Http\Requests\Editor\UpdateContentRequest;
use App\Http\Requests\Editor\UpdateEventsRequest;
use App\Http\Requests\Editor\UpdateGiftsRequest;
use App\Http\Requests\Editor\UpdatePeopleRequest;
use App\Http\Requests\Editor\UpdateSettingsRequest;
use App\Http\Requests\Editor\UpdateStoryRequest;
use App\Models\User;
use Illuminate\Routing\Route;

/** Drops ids and null/empty values so a payload row can be compared with the DTO row. */
function rowSubset(array $payloadRow, array $dtoRow): array
{
    $keys = array_keys(array_filter($payloadRow, fn ($v) => $v !== null));

    return [
        array_intersect_key($payloadRow, array_flip($keys)),
        array_intersect_key($dtoRow, array_flip($keys)),
    ];
}

it('keeps every field through form, model and view-model for each event type', function (EventType $type) {
    $registry = app(EventTypeRegistry::class);
    $def = $registry->for($type);
    $invitation = Invitation::factory()->forType($type)->create();
    $writer = app(InvitationWriter::class);
    $sample = SamplePayload::for($type);

    // 1) form -> validated (only whitelisted keys survive) -> model
    foreach (EventTypeDefinition::STEPS as $step) {
        $validated = Validator::make($sample[$step], $def->rules($step))->validate();
        $writer->apply($invitation, $step, $validated);
    }

    // 2) model -> view-model
    $dto = InvitationData::fromModel($invitation->fresh())->toArray();

    // 3) nothing lost or altered
    foreach (['title', 'template_slug', 'locale', 'timezone'] as $k) {
        expect($dto[$k])->toBe($sample['basics'][$k], "basics.{$k}");
    }
    foreach (['people', 'events', 'story', 'gifts'] as $step) {
        $rows = $sample[$step][$step];
        expect($dto[$step])->toHaveCount(count($rows), $step);
        foreach ($rows as $i => $row) {
            [$expected, $actual] = rowSubset($row, $dto[$step][$i]);
            expect($actual)->toEqual($expected, "{$step}[{$i}] lost or changed a field");
        }
    }
    expect($dto['content'])->toEqual($sample['content']['content']);
    foreach ($sample['settings']['settings'] as $k => $v) {
        expect($dto['settings'][$k])->toBe($v, "settings.{$k}");
    }
    expect($dto['sections'])->toBe($def->sections);
})->with(fn () => EventType::cases());

it('stores event times in UTC and shows them in the invitation timezone', function () {
    $invitation = Invitation::factory()->create(['timezone' => 'Asia/Makassar']); // UTC+8
    app(InvitationWriter::class)->apply($invitation, 'events', ['events' => [[
        'kind' => 'akad', 'starts_at' => '2027-03-14 09:00', 'venue_name' => 'X', 'address' => 'Y',
    ]]]);
    $raw = DB::table('invitation_events')->where('invitation_id', $invitation->id)->value('starts_at');
    expect(substr((string) $raw, 0, 16))->toBe('2027-03-14 01:00');
    expect(InvitationData::fromModel($invitation->fresh())->events[0]->startsAt->format('Y-m-d H:i'))->toBe('2027-03-14 09:00');
});

it('updates children by id and deletes the ones that were removed', function () {
    $invitation = Invitation::factory()->aqiqah()->create();
    $writer = app(InvitationWriter::class);
    $people = SamplePayload::for(EventType::Aqiqah)['people']['people'];
    $writer->apply($invitation, 'people', ['people' => $people]);

    $first = $invitation->people()->get();
    $keepId = $first->firstWhere('role.value', 'baby')->id;
    $payload = [['id' => $keepId, 'role' => 'baby', 'name' => 'Nama Baru'], ['role' => 'parent', 'name' => 'Orang Tua']];
    $writer->apply($invitation, 'people', ['people' => $payload]);

    $after = $invitation->people()->get();
    expect($after)->toHaveCount(2);
    expect($after->firstWhere('id', $keepId)->name)->toBe('Nama Baru');       // id preserved
    expect($first->pluck('id')->diff($after->pluck('id')))->not->toBeEmpty();  // removed rows are gone
});

it('drops content keys the event type does not define', function () {
    $invitation = Invitation::factory()->wedding()->create();
    app(InvitationWriter::class)->apply($invitation, 'content', ['content' => ['opening' => 'Halo', 'theme' => 'bocor']]);
    expect($invitation->fresh()->content)->toBe(['opening' => 'Halo']);
});

it('only exposes defined content keys to templates', function () {
    $invitation = Invitation::factory()->wedding()->create(['content' => ['opening' => 'Halo', 'stale_key' => 'x']]);
    expect(InvitationData::fromModel($invitation)->content)->toBe(['opening' => 'Halo']);
});

dataset('requests', [
    'basics' => [UpdateBasicsRequest::class, 'basics'],
    'people' => [UpdatePeopleRequest::class, 'people'],
    'events' => [UpdateEventsRequest::class, 'events'],
    'story' => [UpdateStoryRequest::class, 'story'],
    'gifts' => [UpdateGiftsRequest::class, 'gifts'],
    'content' => [UpdateContentRequest::class, 'content'],
    'settings' => [UpdateSettingsRequest::class, 'settings'],
]);

it('derives every form request rule from the event type definition', function (string $class, string $step) {
    $owner = User::factory()->create();
    $invitation = Invitation::factory()->for($owner)->birthday()->create();

    $request = $class::create('/x', 'PUT', []);
    $request->setUserResolver(fn () => $owner);
    $route = (new Route('PUT', '/x/{invitation}', []))->bind($request);
    $route->setParameter('invitation', $invitation);
    $request->setRouteResolver(fn () => $route);

    expect($request->authorize())->toBeTrue();
    expect(array_keys($request->rules()))->toEqual(array_keys(app(EventTypeRegistry::class)->for('birthday')->rules($step)));
})->with('requests');

it('refuses a form request from someone who does not own the invitation', function () {
    $invitation = Invitation::factory()->create();
    $stranger = User::factory()->create();
    $request = UpdatePeopleRequest::create('/x', 'PUT', []);
    $request->setUserResolver(fn () => $stranger);
    $route = (new Route('PUT', '/x/{invitation}', []))->bind($request);
    $route->setParameter('invitation', $invitation);
    $request->setRouteResolver(fn () => $route);
    expect($request->authorize())->toBeFalse();
});
