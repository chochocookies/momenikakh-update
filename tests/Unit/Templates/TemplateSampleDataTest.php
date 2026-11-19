<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Templates\SectionRegistry;
use App\Domain\Templates\TemplateRegistry;
use App\Domain\Templates\TemplateSampleData;
use Illuminate\Support\Facades\DB;
use Tests\Support\TemplateFixtures;

beforeEach(function () {
    $this->root = TemplateFixtures::tempRoot();
    TemplateFixtures::make($this->root, 'classic-elegant', TemplateFixtures::manifest('classic-elegant'));
    TemplateFixtures::useRoot($this->root);
    $this->sample = fn (): TemplateSampleData => app(TemplateSampleData::class);
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('builds a complete view-model for every event type without touching the database', function (EventType $type) {
    DB::enableQueryLog();

    $vm = ($this->sample)()->viewModel($type);

    expect(DB::getQueryLog())->toBe([])
        ->and($vm->eventType)->toBe($type)
        ->and($vm->isPreview)->toBeTrue()
        ->and($vm->slug)->toStartWith('contoh-')
        ->and($vm->people)->not->toBeEmpty()
        ->and($vm->schedule)->not->toBeEmpty()
        ->and($vm->cover)->not->toBeNull()
        ->and($vm->gallery)->toHaveCount(6)
        ->and($vm->music)->not->toBeNull()
        ->and($vm->sections[0])->toBe('cover')
        ->and($vm->has('rsvp'))->toBeTrue()->and($vm->has('guestbook'))->toBeTrue()->and($vm->has('map'))->toBeTrue();
})->with(fn () => EventType::cases());

it('fills every section the event type defines', function (EventType $type) {
    $vm = ($this->sample)()->viewModel($type);
    $definition = app(EventTypeRegistry::class)->for($type);

    // The data of a sample is complete, so the only sections missing are the ones the event type does not use
    // (gifts switched off for engagement, aqiqah, khitanan and birthday; story off for aqiqah and khitanan).
    $expected = array_filter(
        array_map(SectionRegistry::normalize(...), $definition->sections),
        fn (string $s) => $s !== 'gift' || $definition->hasFeature('gifts'),
    );
    foreach ($expected as $section) {
        expect($vm->has($section))->toBeTrue("{$type->value} is missing section {$section}");
    }
})->with(fn () => EventType::cases());

it('keeps the sample in the future so countdowns have something to count', function (EventType $type) {
    $vm = ($this->sample)()->viewModel($type);

    expect($vm->firstEvent()->startsAt->isFuture())->toBeTrue()
        ->and($vm->countdownTo())->toMatch('/\+07:00$/');
    foreach ($vm->schedule as $i => $event) {
        if ($i > 0) {
            expect($event->startsAt >= $vm->schedule[0]->startsAt)->toBeTrue();
        }
    }
})->with(fn () => EventType::cases());

it('gives every person a photo and every gift account a QR', function () {
    $vm = ($this->sample)()->viewModel(EventType::Wedding);

    foreach ($vm->people as $person) {
        expect($vm->photoFor($person)?->url)->toBe('/template-sample/portrait.svg');
    }
    foreach ($vm->gifts as $gift) {
        expect($vm->qrFor($gift)?->url)->toBe('/template-sample/qr.svg');
    }
    expect($vm->person(PersonRole::Groom)?->name)->toBe('Raka Pratama');
});

it('uses only obviously fictional contact data', function (EventType $type) {
    $vm = ($this->sample)()->viewModel($type);

    foreach ($vm->gifts as $gift) {
        expect($gift->number)->toBeIn([null, '1234567890', '081200000001']);
    }
    foreach ($vm->people as $person) {
        expect($person->instagram === null || str_ends_with($person->instagram, '.contoh'))->toBeTrue("{$person->name} has a non-fictional handle");
    }
})->with(fn () => EventType::cases());

it('can preview another template, palette and motion', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['palettes' => [['key' => 'rose', 'name' => 'Rose', 'tokens' => ['--t-bg' => '#fff', '--t-ink' => '#000', '--t-accent' => '#111', '--t-orn' => '#222']], ['key' => 'sage', 'name' => 'Sage', 'tokens' => ['--t-bg' => '#fff', '--t-ink' => '#000', '--t-accent' => '#333', '--t-orn' => '#444']]]]));
    app(TemplateRegistry::class)->flush();

    $vm = ($this->sample)()->viewModel(EventType::Wedding, 'soft-rose', 'sage', 'reduced');

    expect($vm->templateSlug)->toBe('soft-rose')->and($vm->palette)->toBe('sage')->and($vm->motion)->toBe('none');
});

it('ships the placeholder files the sample points at', function () {
    foreach (['cover.svg', 'gallery-a.svg', 'gallery-b.svg', 'gallery-c.svg', 'portrait.svg', 'qr.svg', 'silence.mp3'] as $file) {
        expect(is_file(public_path("template-sample/{$file}")))->toBeTrue("missing public/template-sample/{$file}");
    }
});
