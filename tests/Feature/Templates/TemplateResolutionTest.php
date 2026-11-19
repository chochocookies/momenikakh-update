<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\InvitationViewModel;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRenderer;
use Illuminate\Support\Facades\File;
use Illuminate\View\ViewException;
use Tests\Support\TemplateFixtures;

const TPL_TEST_INDEX = <<<'BLADE'
<main data-template="{{ $template->slug }}">
@foreach ($invitation->sections as $section)
<section data-section="{{ $section }}">@invitationSection($section)</section>
@endforeach
</main>
BLADE;

beforeEach(function () {
    $this->root = TemplateFixtures::tempRoot();
    foreach (['classic-elegant', 'soft-rose'] as $slug) {
        TemplateFixtures::make($this->root, $slug, TemplateFixtures::manifest($slug, ['sections' => ['cover', 'opening', 'people', 'closing']]), index: TPL_TEST_INDEX);
    }
    File::ensureDirectoryExists($this->root.'/_shared/sections');
    foreach (['cover', 'opening', 'people', 'closing'] as $section) {
        file_put_contents($this->root."/_shared/sections/{$section}.blade.php", 'SHARED-'.strtoupper($section).' {{ $invitation->title }}');
    }
    // classic-elegant overrides ONLY the cover.
    File::ensureDirectoryExists($this->root.'/classic-elegant/sections');
    file_put_contents($this->root.'/classic-elegant/sections/cover.blade.php', 'CLASSIC-COVER {{ $invitation->title }}');

    TemplateFixtures::useRoot($this->root);
    $this->vm = fn (string $slug): InvitationViewModel => app(InvitationViewModelBuilder::class)->fromModel(
        Invitation::query()->findOrFail(Invitation::factory()->wedding()->complete()->create(['title' => 'Raka & Sinta'])->id),
        template: $slug,
    );
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('uses the template partial when it overrides a section and the shared partial for the rest', function () {
    $html = app(TemplateRenderer::class)->html(($this->vm)('classic-elegant'));

    expect($html)->toContain('data-template="classic-elegant"')
        ->toContain('CLASSIC-COVER')->not->toContain('SHARED-COVER')   // overridden
        ->toContain('SHARED-OPENING')->toContain('SHARED-PEOPLE')->toContain('SHARED-CLOSING'); // inherited
});

it('uses only shared partials for a template without overrides', function () {
    $html = app(TemplateRenderer::class)->html(($this->vm)('soft-rose'));

    expect($html)->toContain('data-template="soft-rose"')->toContain('SHARED-COVER')->not->toContain('CLASSIC-COVER');
});

it('does not leak one template override into another', function () {
    $renderer = app(TemplateRenderer::class);

    expect($renderer->html(($this->vm)('classic-elegant')))->toContain('CLASSIC-COVER')
        ->and($renderer->html(($this->vm)('soft-rose')))->not->toContain('CLASSIC-COVER');
});

it('escapes view-model text inside partials', function () {
    $html = app(TemplateRenderer::class)->html(($this->vm)('soft-rose'));

    expect($html)->toContain('Raka &amp; Sinta')->not->toContain('Raka & Sinta</section>');
});

it('answers which view renders a section', function () {
    $renderer = app(TemplateRenderer::class);

    expect($renderer->sectionView('classic-elegant', 'cover'))->toBe('templates::classic-elegant.sections.cover')
        ->and($renderer->sectionView('classic-elegant', 'opening'))->toBe('templates::_shared.sections.opening')
        ->and($renderer->sectionView('soft-rose', 'cover'))->toBe('templates::_shared.sections.cover')
        ->and($renderer->sectionView('soft-rose', 'confetti'))->toBeNull();
});

it('hands a template exactly the view-model and its manifest, nothing else', function () {
    $data = app(TemplateRenderer::class)->data(($this->vm)('soft-rose'));

    expect(array_keys($data))->toBe(['invitation', 'template'])
        ->and($data['invitation'])->toBeInstanceOf(InvitationViewModel::class);
});

it('fails with a clear message when a section has no partial anywhere', function () {
    unlink($this->root.'/_shared/sections/closing.blade.php');

    expect(fn () => app(TemplateRenderer::class)->html(($this->vm)('soft-rose')))
        ->toThrow(ViewException::class, 'None of the views in the given array exist.'); // Blade wraps the InvalidArgumentException
});
