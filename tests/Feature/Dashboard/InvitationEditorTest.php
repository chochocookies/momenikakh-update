<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\TemplateRegistry;
use App\Livewire\Editor\InvitationEditor;
use App\Livewire\Editor\StepFields;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    // Livewire posts its updates to a relative path; the update route only exists on the app host, so requests must go there.
    URL::forceRootUrl(Surfaces::url('app', ''));
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->create(['title' => 'Raka & Sinta']);
    $this->editor = fn (?Invitation $i = null) => Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $i ?? $this->invitation]);
});

/** Rule paths of a step that carry a user-editable value: no containers, ids, or the fixed locale. */
function editablePaths(string $step, EventTypeDefinition $definition): array
{
    $paths = [];
    foreach (array_keys($definition->rules($step)) as $path) {
        $isContainer = in_array($path, ['people', 'events', 'story', 'gifts', 'content', 'settings'], true);
        if ($isContainer || str_ends_with($path, '.id') || $path === 'locale') {
            continue;
        }
        $paths[] = $path;
    }
    sort($paths);

    return $paths;
}

it('asks a visitor to sign in and keeps other people out', function () {
    $url = route('dashboard.invitations.edit', $this->invitation);

    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs($this->stranger)->get($url)->assertForbidden();
    $this->actingAs($this->owner)->get($url)->assertOk()->assertSee('Isi data undangan')->assertSee('Raka &amp; Sinta', false);
});

it('is reachable from the invitation page', function () {
    $html = $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $this->invitation))->getContent();

    expect($html)->toContain(route('dashboard.invitations.edit', $this->invitation, false));
});

it('has no field missing: every rule of every step has a field, and every field has a rule', function (EventType $type) {
    $registry = app(EventTypeRegistry::class);
    $templates = app(TemplateRegistry::class);
    $definition = $registry->for($type);
    $invitation = Invitation::factory()->for($this->owner)->forType($type)->complete()->create();

    foreach (StepFields::steps($definition) as $step => $label) {
        $fields = array_keys(StepFields::labels($step, $definition, $invitation, $templates));
        sort($fields);

        expect($fields)->toBe(editablePaths($step, $definition), "step [{$step}] of [{$type->value}]");
    }
    // Only the optional steps can be hidden, and a hidden story step is refused by its rules.
    $hidden = array_diff(EventTypeDefinition::STEPS, array_keys(StepFields::steps($definition)));
    expect(array_diff($hidden, ['story', 'gifts']))->toBe([]);
    if (in_array('story', $hidden, true)) {
        expect($definition->rules('story'))->toBe(['story' => ['prohibited']]);
    }
})->with(fn () => EventType::cases());

it('renders every field of every step in the page', function (EventType $type) {
    $invitation = Invitation::factory()->for($this->owner)->forType($type)->complete()->create();
    $definition = app(EventTypeRegistry::class)->for($type);
    $component = ($this->editor)($invitation);

    foreach (array_keys(StepFields::steps($definition)) as $step) {
        $component->call('selectStep', $step);
        $html = $component->html();
        $paths = in_array($step, StepFields::LISTS, true)
            ? array_map(fn (string $p) => 'form.'.str_replace('*', '0', $p), editablePaths($step, $definition))
            : array_map(fn (string $p) => 'form.'.$p, editablePaths($step, $definition));

        foreach ($paths as $path) {
            expect($html)->toContain('data-field="'.$path.'"');
        }
    }
})->with(fn () => EventType::cases());

it('saves a field when it changes and shows the time it was saved', function () {
    ($this->editor)()->set('form.title', 'Raka dan Sinta')->assertHasNoErrors()->assertSee('Tersimpan otomatis pukul');

    expect($this->invitation->fresh()->title)->toBe('Raka dan Sinta');
});

it('shows the error under the field and keeps the saved data when the value is invalid', function () {
    ($this->editor)()
        ->set('form.title', '')
        ->assertHasErrors(['form.title'])
        ->assertSee('Judul undangan wajib diisi.')
        ->assertSee('belum tersimpan');

    expect($this->invitation->fresh()->title)->toBe('Raka & Sinta');
});

it('saves once the invalid field is fixed', function () {
    ($this->editor)()->set('form.title', '')->assertHasErrors(['form.title'])->set('form.title', 'Judul baru')->assertHasNoErrors();

    expect($this->invitation->fresh()->title)->toBe('Judul baru');
});

it('refuses a zone, template or palette that is not on the list', function (string $path, string $value) {
    ($this->editor)()->set($path, $value)->assertHasErrors([$path]);
})->with([
    'zone' => ['form.timezone', 'Asia/Tokyo'],
    'template' => ['form.template_slug', 'tidak-ada'],
    'palette' => ['form.settings.palette', 'tidak-ada'],
    'motion' => ['form.settings.motion', 'wild'],
]);

it('edits a person and keeps its id', function () {
    $person = $this->invitation->people()->orderBy('sort')->firstOrFail();

    ($this->editor)()->set('form.people.0.name', 'Raka Pratama')->assertHasNoErrors()->assertSet('form.people.0.id', $person->id);

    expect($person->fresh()->name)->toBe('Raka Pratama');
});

it('turns an instagram handle with a leading @ into an error, not into data', function () {
    ($this->editor)()->set('form.people.0.instagram', '@raka')->assertHasErrors(['form.people.0.instagram']);
});

it('enforces the number of people per role', function () {
    $component = ($this->editor)();
    $groomCount = count(array_filter($component->get('form.people'), fn ($p) => $p['role'] === 'groom'));
    $component->set('form.people.0.role', 'bride')->assertHasErrors(['form.people']);

    expect($groomCount)->toBe(1);
});

it('adds an event, saves it in UTC from the wall-clock time of its own zone, and gives it an id', function () {
    $component = ($this->editor)()->call('selectStep', 'events')->call('addRow', 'events');
    $index = count($component->get('form.events')) - 1;

    $component
        ->set("form.events.{$index}.starts_at", '2026-12-05T08:00')
        ->set("form.events.{$index}.venue_name", 'Gedung Contoh')
        ->set("form.events.{$index}.address", 'Jl. Contoh No. 1')
        ->set("form.events.{$index}.timezone", 'Asia/Makassar')
        ->assertHasNoErrors();

    $event = $this->invitation->events()->reorder('id', 'desc')->firstOrFail();
    expect($event->venue_name)->toBe('Gedung Contoh')
        ->and($event->timezone)->toBe('Asia/Makassar')
        ->and($event->starts_at->utc()->format('Y-m-d H:i'))->toBe('2026-12-05 00:00')
        ->and($component->get("form.events.{$index}.id"))->toBe($event->id)
        ->and($component->get("form.events.{$index}.starts_at"))->toBe('2026-12-05T08:00');
});

it('refuses an event that ends before it starts and a map link without https', function () {
    ($this->editor)()->call('selectStep', 'events')
        ->set('form.events.0.ends_at', '2000-01-01T00:00')->assertHasErrors(['form.events.0.ends_at'])
        ->set('form.events.0.ends_at', '')->set('form.events.0.maps_url', 'http://contoh.test')->assertHasErrors(['form.events.0.maps_url']);
});

it('removes a row and saves right away', function () {
    $before = $this->invitation->storyItems()->count();
    expect($before)->toBeGreaterThan(0);

    ($this->editor)()->call('selectStep', 'story')->call('removeRow', 'story', 0)->assertHasNoErrors();

    expect($this->invitation->storyItems()->count())->toBe($before - 1);
});

it('does not save a removal that would go below the minimum', function () {
    $count = $this->invitation->events()->count();
    $component = ($this->editor)()->call('selectStep', 'events');
    foreach (range(1, $count) as $_) {
        $component->call('removeRow', 'events', 0);
    }

    $component->assertHasErrors(['form.events']);
    expect($this->invitation->events()->count())->toBeGreaterThanOrEqual(1);
});

it('adds and edits a gift, and asks for the account number only when it is a bank or e-wallet', function () {
    $component = ($this->editor)()->call('selectStep', 'gifts')->call('addRow', 'gifts');
    $index = count($component->get('form.gifts')) - 1;

    $component->set("form.gifts.{$index}.provider", 'Bank Contoh')->assertHasErrors(["form.gifts.{$index}.number", "form.gifts.{$index}.holder_name"]);
    $component->set("form.gifts.{$index}.holder_name", 'Raka Pratama')->set("form.gifts.{$index}.number", '1234567890')->assertHasNoErrors();

    expect($this->invitation->gifts()->where('number', '1234567890')->exists())->toBeTrue();
});

it('shows no story step for an event type without it', function () {
    $invitation = Invitation::factory()->for($this->owner)->aqiqah()->complete()->create();
    $definition = app(EventTypeRegistry::class)->for(EventType::Aqiqah);

    expect($definition->hasFeature('story'))->toBeFalse()->and(StepFields::steps($definition))->not->toHaveKey('story');
    ($this->editor)($invitation)->call('selectStep', 'story')->assertSet('step', 'basics');
});

it('saves every step unchanged without altering anything: the form and the writer agree', function (EventType $type) {
    $invitation = Invitation::factory()->for($this->owner)->forType($type)->complete()->create();
    $before = InvitationData::fromModel($invitation->fresh())->toArray();
    $component = ($this->editor)($invitation);

    foreach (array_keys(StepFields::steps(app(EventTypeRegistry::class)->for($type))) as $step) {
        // Event types without a template cannot pick one: the basics step is refused for them on purpose.
        if ($step === 'basics' && app(TemplateRegistry::class)->forEvent($type) === []) {
            continue;
        }
        expect($component->call('save', $step)->errors()->all())->toBe([], "step [{$step}] of [{$type->value}]");
    }

    $after = InvitationData::fromModel($invitation->fresh())->toArray();

    // The sample data names a palette ("default") that no template has; the editor shows and saves the template's first one.
    // Content keys have no order of their own, so they are compared as sets.
    $palettes = app(TemplateRegistry::class)->get('classic-elegant')->paletteKeys();
    expect($after['settings']['palette'])->toBeIn($palettes);
    unset($before['settings']['palette'], $after['settings']['palette']);
    expect($after)->toEqual($before);
})->with(fn () => EventType::cases());

it('writes content text and removes it again when the field is emptied', function () {
    $component = ($this->editor)()->call('selectStep', 'content')->set('form.content.opening', 'Dengan memohon rahmat Allah.');
    expect($this->invitation->fresh()->content['opening'])->toBe('Dengan memohon rahmat Allah.');

    $component->set('form.content.opening', '')->assertHasNoErrors();
    expect($this->invitation->fresh()->content)->not->toHaveKey('opening');
});

it('keeps content within the length the event type allows', function () {
    $max = app(EventTypeRegistry::class)->for(EventType::Wedding)->content['opening'];

    ($this->editor)()->call('selectStep', 'content')->set('form.content.opening', str_repeat('a', $max + 1))->assertHasErrors(['form.content.opening']);
});

it('saves the display settings and resets the palette when the template does not have it', function () {
    ($this->editor)()->call('selectStep', 'settings')->set('form.settings.motion', 'reduced')->set('form.settings.rsvp_enabled', false)->assertHasNoErrors();

    $settings = $this->invitation->fresh()->settings;
    expect($settings['motion'])->toBe('reduced')->and($settings['rsvp_enabled'])->toBeFalse();
});

it('only follows a step name the event type has', function () {
    ($this->editor)()->call('selectStep', 'nonsense')->assertSet('step', 'basics')->call('selectStep', 'people')->assertSet('step', 'people');
});

it('does not let anyone else save through a tampered component', function () {
    $component = Livewire::actingAs($this->stranger)->test(InvitationEditor::class, ['invitation' => $this->invitation]);

    $component->assertForbidden();
    expect($this->invitation->fresh()->title)->toBe('Raka & Sinta');
});

it('answers the Livewire endpoint on the app host only', function () {
    // No POST handler on these hosts: 404, or 405 when a GET route shares the path.
    foreach (['marketing', 'public'] as $surface) {
        expect($this->post(Surfaces::url($surface, '/livewire/update'))->getStatusCode())->toBeIn([404, 405]);
    }
});

it('serves the CSP-safe Livewire build and keeps the app script policy free of unsafe-eval', function () {
    $html = $this->actingAs($this->owner)->get(route('dashboard.invitations.edit', $this->invitation));

    expect(config('livewire.csp_safe'))->toBeTrue()
        ->and($html->headers->get('Content-Security-Policy'))->toContain("script-src 'self'")->not->toContain('unsafe-eval');
});
