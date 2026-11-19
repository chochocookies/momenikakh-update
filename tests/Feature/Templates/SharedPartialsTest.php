<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\InvitationViewModel;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\SectionRegistry;
use App\Domain\Templates\TemplateRegistry;
use App\Domain\Templates\TemplateSampleData;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    View::share('errors', new ViewErrorBag);
    /** Renders one shared partial with the real registry (resources/templates). */
    $this->partial = fn (string $section, InvitationViewModel $vm): string => view("templates::_shared.sections.{$section}", [
        'invitation' => $vm, 'template' => app(TemplateRegistry::class)->get($vm->templateSlug),
    ])->render();
    $this->sample = fn (EventType $type): InvitationViewModel => app(TemplateSampleData::class)->viewModel($type, 'classic-elegant');
});

it('has a shared partial for every section key', function () {
    foreach (SectionRegistry::KEYS as $key) {
        expect(is_file(resource_path("templates/_shared/sections/{$key}.blade.php")))->toBeTrue("missing partial {$key}");
    }
});

it('renders every shared partial from the sample data of every event type', function (EventType $type) {
    $vm = ($this->sample)($type);
    $rendered = 0;

    foreach ($vm->sections as $section) {
        $html = ($this->partial)($section, $vm);
        expect(trim($html))->not->toBe('', "{$type->value}/{$section} rendered nothing");
        $rendered++;
    }

    expect($rendered)->toBeGreaterThanOrEqual(7);
})->with(fn () => EventType::cases());

it('covers all 13 partials with the wedding sample', function () {
    $vm = ($this->sample)(EventType::Wedding);

    expect($vm->sections)->toEqualCanonicalizing(SectionRegistry::KEYS);
});

it('produces accessible, script-free markup in every partial', function (EventType $type) {
    $vm = ($this->sample)($type);
    $problems = [];

    foreach ($vm->sections as $section) {
        $html = ($this->partial)($section, $vm);
        $xp = DesignSystem::xpath($html);

        foreach ($xp->query('//img') as $img) {
            foreach (['alt', 'width', 'height'] as $attr) {
                if (! $img->hasAttribute($attr)) {
                    $problems[] = "{$section}: <img> lacks {$attr}";
                }
            }
        }
        foreach ($xp->query('//button') as $button) {
            if (! $button->hasAttribute('type')) {
                $problems[] = "{$section}: <button> without type";
            }
            if (trim($button->textContent) === '' && ! $button->hasAttribute('aria-label')) {
                $problems[] = "{$section}: icon-only <button> without aria-label";
            }
        }
        foreach ($xp->query('//input[not(@type="hidden")]|//textarea|//select') as $control) {
            $id = $control->getAttribute('id');
            $labelled = $control->hasAttribute('aria-label')
                || ($id !== '' && $xp->query('//label[@for="'.$id.'"]')->length > 0)
                || $xp->query('ancestor::label', $control)->length > 0;
            if (! $labelled) {
                $problems[] = "{$section}: unlabelled <{$control->nodeName}> ".$control->getAttribute('name');
            }
        }
        foreach ($xp->query('//a[@target="_blank"]') as $link) {
            if (! str_contains($link->getAttribute('rel'), 'noopener')) {
                $problems[] = "{$section}: target=_blank without noopener";
            }
        }
        if ($xp->query('//script|//style|//*[@style]')->length > 0) {
            $problems[] = "{$section}: inline script or style";
        }
        if (preg_match('/\son(?:click|load|error|change|submit|focus|blur)\s*=/i', $html)) {
            $problems[] = "{$section}: inline event handler";
        }
        foreach ($xp->query('//section[@aria-labelledby]') as $sectionNode) {
            if ($xp->query('//*[@id="'.$sectionNode->getAttribute('aria-labelledby').'"]')->length !== 1) {
                $problems[] = "{$section}: aria-labelledby points nowhere";
            }
        }
    }

    expect($problems)->toBe([]);
})->with(fn () => EventType::cases());

it('keeps ids unique and headings in order on a whole page', function (EventType $type) {
    $vm = ($this->sample)($type);
    $html = implode("\n", array_map(fn (string $s) => ($this->partial)($s, $vm), $vm->sections));
    $xp = DesignSystem::xpath($html);

    $ids = [];
    foreach ($xp->query('//*[@id]') as $el) {
        $ids[] = $el->getAttribute('id');
    }
    expect(array_keys(array_filter(array_count_values($ids), fn ($n) => $n > 1)))->toBe([])
        ->and($xp->query('//h1')->length)->toBe(1);

    $previous = 0;
    foreach ($xp->query('//h1|//h2|//h3') as $heading) {
        $level = (int) substr($heading->nodeName, 1);
        expect($previous === 0 || $level <= $previous + 1)->toBeTrue("heading {$heading->nodeName} after h{$previous}");
        $previous = $level;
    }
})->with(fn () => EventType::cases());

it('gives the cover a guest slot that holds only the neutral placeholder', function () {
    $html = ($this->partial)('cover', ($this->sample)(EventType::Wedding));

    expect($html)->toContain('data-guest')->toContain('data-guest-default="Tamu Undangan"')
        ->toContain('>Tamu Undangan</strong>')->toContain('data-open-invitation');
});

it('disables the forms in a preview and enables them for a real invitation', function () {
    $preview = ($this->sample)(EventType::Wedding);
    expect(($this->partial)('rsvp', $preview))->toContain('<fieldset class="inv-form__fields" disabled>')->toContain('Ini pratinjau')
        ->and(($this->partial)('guestbook', $preview))->toContain('disabled');

    $real = app(InvitationViewModelBuilder::class)->fromModel(Invitation::factory()->wedding()->complete()->create());
    expect(($this->partial)('rsvp', $real))->not->toContain('disabled')->not->toContain('Ini pratinjau')
        ->toContain('name="attendance" value="yes"')->toContain('name="website"');
});

it('escapes every owner-written text, so hostile input stays text', function () {
    $hostile = '<img src=x onerror=alert(1)>';
    $invitation = Invitation::factory()->wedding()->complete()->create(['title' => $hostile]);
    $invitation->people()->update(['name' => $hostile, 'parents_text' => $hostile, 'nickname' => $hostile]);
    $invitation->events()->update(['venue_name' => $hostile, 'address' => $hostile, 'notes' => $hostile]);
    $invitation->gifts()->update(['holder_name' => $hostile, 'provider' => $hostile]);
    $invitation->update(['content' => ['opening' => $hostile, 'quote' => $hostile, 'closing' => $hostile]]);

    $vm = app(InvitationViewModelBuilder::class)->fromModel(Invitation::query()->findOrFail($invitation->id));
    $html = implode("\n", array_map(fn (string $s) => ($this->partial)($s, $vm), $vm->sections));

    expect($html)->not->toContain('<img src=x')->toContain('&lt;img src=x onerror=alert(1)&gt;')
        ->and(DesignSystem::xpath($html)->query('//img[@onerror]')->length)->toBe(0);
});

it('only links maps and photos to safe targets', function () {
    $vm = ($this->sample)(EventType::Wedding);
    $html = ($this->partial)('map', $vm);

    expect($html)->toContain('href="https://maps.google.com/')->toContain('rel="noopener noreferrer"')->not->toContain('javascript:');
});

it('renders the music toggle only with music and shows its attribution in the closing', function () {
    $vm = ($this->sample)(EventType::Wedding);

    expect(($this->partial)('music', $vm))->toContain('data-music-toggle')->toContain('aria-pressed="false"')->toContain('aria-label="Putar musik"');
    expect($vm->has('music'))->toBeTrue();
});
