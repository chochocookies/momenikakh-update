<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Models\Invitation;
use App\Livewire\Editor\InvitationEditor;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\Support\Surfaces;
use Tests\Support\TemplateFixtures;

beforeEach(function () {
    $this->withoutVite();
    URL::forceRootUrl(Surfaces::url('app', ''));
    $this->root = TemplateFixtures::tempRoot();
    foreach (['classic-elegant' => 'Classic Elegant', 'soft-rose' => 'Soft Rose'] as $slug => $name) {
        TemplateFixtures::make($this->root, $slug, TemplateFixtures::manifest($slug, [
            'name' => $name,
            'palettes' => [['key' => "{$slug}-a", 'name' => 'A', 'tokens' => ['--t-bg' => '#FFFFFF', '--t-ink' => '#000000', '--t-accent' => '#7B1E3A', '--t-orn' => '#B8893B']],
                ['key' => "{$slug}-b", 'name' => 'B', 'tokens' => ['--t-bg' => '#FFFFFF', '--t-ink' => '#000000', '--t-accent' => '#7B1E3A', '--t-orn' => '#B8893B']]],
        ]));
    }
    TemplateFixtures::useRoot($this->root, withShared: true);
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->create(['template_slug' => 'classic-elegant']);
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

/** Everything the owner wrote, minus the template and the palette (which belong to the template). */
function ownerData(Invitation $invitation): array
{
    $data = InvitationData::fromModel($invitation->fresh())->toArray();
    unset($data['template_slug'], $data['settings']['palette']);

    return $data;
}

it('switches the template and keeps every piece of data the owner wrote', function () {
    $before = ownerData($this->invitation);

    Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('form.template_slug', 'soft-rose')->assertHasNoErrors();

    $this->invitation->refresh();
    expect($this->invitation->template_slug)->toBe('soft-rose')->and(ownerData($this->invitation))->toEqual($before);
});

it('keeps the palette when the new template has one with that key, and falls back to its first otherwise', function () {
    $component = Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $this->invitation]);

    $component->set('form.template_slug', 'soft-rose');
    expect($this->invitation->fresh()->settings['palette'])->toBe('soft-rose-a');

    $component->call('selectStep', 'settings')->set('form.settings.palette', 'soft-rose-b')->set('form.template_slug', 'classic-elegant');
    expect($this->invitation->fresh()->settings['palette'])->toBe('classic-elegant-a');
});

it('refuses a template that does not exist or does not fit the event type', function () {
    TemplateFixtures::make($this->root, 'only-birthday', TemplateFixtures::manifest('only-birthday', ['event_types' => ['birthday']]));
    TemplateFixtures::useRoot($this->root, withShared: true);

    Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $this->invitation])
        ->set('form.template_slug', 'only-birthday')->assertHasErrors(['form.template_slug'])
        ->set('form.template_slug', 'nowhere')->assertHasErrors(['form.template_slug']);

    expect($this->invitation->fresh()->template_slug)->toBe('classic-elegant');
});
