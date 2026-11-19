<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Templates\InvitationViewModel;
use App\Domain\Templates\InvitationViewModelBuilder;
use Tests\Support\TemplateFixtures;

beforeEach(function () {
    $this->root = TemplateFixtures::tempRoot();
    TemplateFixtures::make($this->root, 'classic-elegant', TemplateFixtures::manifest('classic-elegant', ['name' => 'Classic Elegant']));
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', [
        'sections' => ['cover', 'people', 'events', 'rsvp', 'closing'],
    ]));
    TemplateFixtures::useRoot($this->root);
    $this->builder = fn (): InvitationViewModelBuilder => app(InvitationViewModelBuilder::class);
    /** Loads a fresh instance, so lazy loading is not accidentally satisfied by factory leftovers. */
    $this->build = fn (Invitation $i, ...$args) => ($this->builder)()->fromModel(Invitation::query()->findOrFail($i->id), ...$args);
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('builds a read-only view-model for every event type without lazy loading', function (EventType $type) {
    $invitation = Invitation::factory()->forType($type)->complete()->published()->create();

    $vm = ($this->build)($invitation);

    expect($vm)->toBeInstanceOf(InvitationViewModel::class)
        ->and($vm->eventType)->toBe($type)
        ->and($vm->slug)->toBe($invitation->slug)
        ->and($vm->templateSlug)->toBe('classic-elegant')
        ->and($vm->sections[0])->toBe('cover')
        ->and($vm->has('people'))->toBeTrue()->and($vm->has('events'))->toBeTrue()
        ->and($vm->people)->not->toBeEmpty()->and($vm->schedule)->not->toBeEmpty()
        ->and($vm->countdownTo())->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/');
})->with(fn () => EventType::cases());

it('carries no guest name: the server renders a neutral placeholder only', function () {
    $vm = ($this->build)(Invitation::factory()->complete()->create());

    expect($vm->guestPlaceholder)->toBe('Tamu Undangan')
        ->and(array_keys(get_object_vars($vm)))->not->toContain('guest', 'guestName', 'to');
});

it('shows the starter copy until the owner writes their own text', function () {
    $empty = ($this->build)(Invitation::factory()->wedding()->create(['content' => ['opening' => '   ']]));
    $own = ($this->build)(Invitation::factory()->wedding()->create(['content' => ['opening' => 'Teks saya']]));

    expect($empty->text('opening'))->toContain('Dengan penuh syukur')
        ->and($own->text('opening'))->toBe('Teks saya')
        ->and($own->text('quote'))->toBeNull();
});

it('only renders sections the data can fill', function () {
    $bare = ($this->build)(Invitation::factory()->wedding()->create());
    expect($bare->sections)->toBe(['cover', 'opening', 'people', 'rsvp', 'guestbook', 'closing']); // no events, story, gallery, gifts, map, music

    $full = ($this->build)(Invitation::factory()->wedding()->complete()->create());
    expect($full->sections)->toBe(['cover', 'opening', 'people', 'events', 'story', 'gift', 'rsvp', 'guestbook', 'countdown', 'map', 'closing'])
        ->and($full->hasMap())->toBeTrue();
});

it('follows the order and the sections of the chosen template', function () {
    $invitation = Invitation::factory()->wedding()->complete()->create();
    $invitation->update(['template_slug' => 'soft-rose']); // complete() sets the sample template, so choose ours afterwards

    $vm = ($this->build)($invitation);

    expect($vm->templateSlug)->toBe('soft-rose')->and($vm->motion)->toBe('full')
        ->and($vm->sections)->toBe(['cover', 'people', 'events', 'rsvp', 'closing']);
});

it('lets a preview override the template without touching the invitation', function () {
    $invitation = Invitation::factory()->wedding()->complete()->create();

    $vm = ($this->build)($invitation, true, 'soft-rose');

    expect($vm->templateSlug)->toBe('soft-rose')->and($vm->isPreview)->toBeTrue()
        ->and($invitation->fresh()->template_slug)->toBe('classic-elegant');
});

it('falls back to the default template when the stored one is gone', function () {
    $vm = ($this->build)(Invitation::factory()->wedding()->create(['template_slug' => 'deleted-long-ago']));

    expect($vm->templateSlug)->toBe('classic-elegant');
});

it('respects the owner switches for rsvp, wishes, gifts and reduced motion', function () {
    $invitation = Invitation::factory()->wedding()->complete()->create();
    $invitation->update(['settings' => ['rsvp_enabled' => false, 'wishes_enabled' => false, 'gifts_enabled' => false, 'motion' => 'reduced', 'palette' => 'sage']]);

    $vm = ($this->build)($invitation);

    expect($vm->has('rsvp'))->toBeFalse()->and($vm->has('guestbook'))->toBeFalse()->and($vm->has('gift'))->toBeFalse()
        ->and($vm->rsvpEnabled)->toBeFalse()
        ->and($vm->motion)->toBe('none')->and($vm->palette)->toBe('rose');
});

it('hides inactive gift accounts and exposes the rest', function () {
    $invitation = Invitation::factory()->wedding()->complete()->create();
    InvitationGift::query()->where('invitation_id', $invitation->id)->where('type', 'bank')->update(['is_active' => false]);

    $vm = ($this->build)($invitation);

    expect(array_map(fn ($g) => $g->type->value, $vm->gifts))->toBe(['ewallet', 'address']);
});

it('finds people by role and builds the canonical and beacon urls', function () {
    $vm = ($this->build)(Invitation::factory()->wedding()->complete()->create(['slug' => 'raka-sinta-abcd']));

    expect($vm->person(PersonRole::Groom)?->name)->toBe('Raka Pratama')
        ->and($vm->person(PersonRole::Bride)?->name)->toBe('Sinta Maharani')
        ->and($vm->person(PersonRole::Baby))->toBeNull()
        ->and($vm->urls['canonical'])->toEndWith('/raka-sinta-abcd')
        ->and($vm->urls['opened'])->toEndWith('/raka-sinta-abcd/opened');
});

it('throws a clear error when not even the default template exists', function () {
    TemplateFixtures::cleanup($this->root);
    TemplateFixtures::useRoot($this->root = TemplateFixtures::tempRoot());

    expect(fn () => ($this->build)(Invitation::factory()->create()))->toThrow(InvalidArgumentException::class, 'Unknown template [classic-elegant].');
});
