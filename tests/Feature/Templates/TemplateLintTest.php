<?php

declare(strict_types=1);

use App\Domain\Templates\Contrast;
use App\Domain\Templates\TemplateLinter;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Support\Facades\File;
use Tests\Support\TemplateFixtures;

/** A throw-away templates folder with the shared partials every manifest section needs. */
function lintRoot(): string
{
    $root = TemplateFixtures::tempRoot();
    File::ensureDirectoryExists("{$root}/_shared/sections");
    foreach (['cover', 'opening', 'people', 'events', 'story', 'gallery', 'gift', 'rsvp', 'guestbook', 'countdown', 'map', 'music', 'closing'] as $section) {
        file_put_contents("{$root}/_shared/sections/{$section}.blade.php", '');
    }
    TemplateFixtures::useRoot($root);

    return $root;
}

function goodTheme(string $slug = 'soft-rose', string $extra = ''): string
{
    return "[data-theme='{$slug}'] { --t-bg: var(--color-ivory); --t-ink: var(--color-ink); --t-accent: var(--color-rose); }\n{$extra}";
}

beforeEach(function () {
    $this->root = lintRoot();
    $this->template = function (array $manifest = [], ?string $css = null, array $files = []) {
        TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', $manifest));
        file_put_contents("{$this->root}/soft-rose/theme.css", $css ?? goodTheme());
        foreach ($files as $path => $content) {
            File::ensureDirectoryExists(dirname("{$this->root}/soft-rose/{$path}"));
            file_put_contents("{$this->root}/soft-rose/{$path}", $content);
        }
    };
    $this->lint = fn (): array => app(TemplateLinter::class)->lint(app(TemplateRegistry::class)->get('soft-rose'));
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('passes a clean template and reports its weight', function () {
    ($this->template)();
    $result = ($this->lint)();

    expect($result['errors'])->toBe([])->and($result['stats'])->toHaveKeys(['css_kb', 'js_kb', 'assets_kb']);
});

it('passes the real Classic Elegant with no errors', function () {
    TemplateFixtures::cleanup($this->root);
    config(['templates.path' => resource_path('templates'), 'templates.cache' => storage_path('framework/templates-test-cache.php')]);
    app()->forgetInstance(TemplateRegistry::class);
    app()->forgetInstance(TemplateLinter::class);

    $registry = app(TemplateRegistry::class);
    foreach ($registry->scan() as $manifest) {
        expect(app(TemplateLinter::class)->lint($manifest)['errors'])->toBe([], "{$manifest->slug} has lint errors");
    }
});

it('fails a template that is heavier than its manifest budget', function () {
    // Random hex does not compress, so these really are over 1 KB gzipped.
    ($this->template)(
        ['budget' => ['css_kb' => 1, 'js_kb' => 1, 'assets_kb' => 1]],
        goodTheme('soft-rose', '/* '.bin2hex(random_bytes(3000)).' */'),
        ['theme.js' => '// '.bin2hex(random_bytes(3000)), 'assets/big.svg' => '<!-- '.bin2hex(random_bytes(1500)).' -->']
    );

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('theme.css weighs')->toContain('theme.js weighs')->toContain('assets/ weighs')->toContain('(budget.css_kb)');
});

it('rejects extra fonts: @font-face, a literal family and font files', function () {
    ($this->template)([], goodTheme('soft-rose', "@font-face { font-family: 'Lobster'; src: url('l.woff2'); }\n.x { font-family: 'Lobster', cursive; }"), ['assets/lobster.woff2' => 'x']);

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('declares @font-face')->toContain('font-family that is not a token')->toContain('Font file assets/lobster.woff2');
});

it('allows font-family only through tokens', function () {
    ($this->template)([], goodTheme('soft-rose', '.x { font-family: var(--t-font-display); } .y { font-family: inherit; }'));

    expect(implode("\n", ($this->lint)()['errors']))->not->toContain('font-family');
});

it('rejects anything loaded from outside and any JavaScript package import', function () {
    ($this->template)([], goodTheme('soft-rose', "@import url('https://fonts.googleapis.com/css2?family=Lobster');"), [
        'theme.js' => "import gsap from 'gsap';\nimport { x } from './local.js';\nfetch('https://cdn.example.com/a.js');",
        'sections/cover.blade.php' => '<script src="https://cdn.example.com/x.js"></script>',
    ]);

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('theme.css references an external address')->toContain('theme.js references an external address')
        ->toContain('imports the package [gsap]')->not->toContain('[./local.js]')
        ->toContain('sections/cover.blade.php loads a script or stylesheet from an external address');
});

it('rejects raw colours in theme.css but allows them in comments', function () {
    ($this->template)([], goodTheme('soft-rose', "/* was #BE185D */\n.x { color: #BE185D; }"));

    expect(implode("\n", ($this->lint)()['errors']))->toContain('raw colour #BE185D');

    ($this->template)([], goodTheme('soft-rose', '/* was #BE185D */ .x { color: var(--color-rose); }'));
    expect(implode("\n", ($this->lint)()['errors']))->not->toContain('raw colour');
});

it('rejects a palette whose text is below 4.5 : 1 on its background', function () {
    ($this->template)(['palettes' => [
        ['key' => 'pale', 'name' => 'Pale', 'tokens' => ['--t-bg' => '#FFFFFF', '--t-ink' => '#BBBBBB', '--t-accent' => '#CCCCCC', '--t-orn' => '#DDDDDD']],
    ]]);

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('Palette [pale]: ink on bg')->toContain('accent on bg')->toContain('accent-ink on accent');
});

it('checks every palette, not only the first', function () {
    $ok = ['--t-bg' => '#FFFFFF', '--t-ink' => '#000000', '--t-accent' => '#7B1E3A', '--t-orn' => '#B8893B'];
    ($this->template)(['palettes' => [
        ['key' => 'good', 'name' => 'Good', 'tokens' => $ok],
        ['key' => 'bad', 'name' => 'Bad', 'tokens' => ['--t-ink' => '#EEEEEE'] + $ok],
    ]]);

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('Palette [bad]: ink on bg')->not->toContain('Palette [good]');
});

it('fails when a manifest section has no partial anywhere', function () {
    unlink("{$this->root}/_shared/sections/map.blade.php");
    ($this->template)();

    expect(implode("\n", ($this->lint)()['errors']))->toContain('Section [map] has no partial');
});

it('rejects unlabelled pictures and too many ornaments in one section', function () {
    ($this->template)([], null, [
        'sections/cover.blade.php' => '<img src="/a.svg"><img src="/b.svg" alt="" aria-hidden="true">',
        'ornaments/cover.blade.php' => '<x-ornament name="a" /><x-ornament name="b" /><x-ornament name="c" /><x-ornament name="d" /><x-ornament name="a" />',
        'ornaments/closing.blade.php' => str_repeat('<x-ornament name="float-petal" layer="float" />', 13),
    ]);

    $errors = implode("\n", ($this->lint)()['errors']);

    expect($errors)->toContain('sections/cover.blade.php: <img> without alt')
        ->toContain('ornaments/cover.blade.php uses 4 different ornaments')
        ->toContain('ornaments/closing.blade.php has 13 floating ornaments');
});

it('warns about missing licence and catalogue images, which do not fail the lint', function () {
    ($this->template)([], null, ['assets/swirl.svg' => '<svg/>']);
    $result = ($this->lint)();

    expect($result['errors'])->toBe([])
        ->and(implode("\n", $result['warnings']))->toContain('assets/LICENSE.md is missing')->toContain('thumb.webp is missing')->toContain('preview.webp is missing');
});

it('computes WCAG contrast', function () {
    expect(Contrast::ratio('#000000', '#FFFFFF'))->toBe(21.0)
        ->and(round(Contrast::ratio('#FFFFFF', '#FFFFFF'), 2))->toBe(1.0)
        ->and(Contrast::ratio('#7B1E3A', '#FBF5EC'))->toBeGreaterThan(4.5)
        ->and(Contrast::ratio('#F2D08A', '#FFF8F3'))->toBeLessThan(3.0); // gold on ivory: decoration only
});

it('exits non-zero from the command when a template has errors, zero when clean', function () {
    ($this->template)([], goodTheme('soft-rose', '.x { color: #BE185D; }'));
    $this->artisan('template:lint', ['slug' => 'soft-rose'])->expectsOutputToContain('raw colour')->assertFailed();

    ($this->template)();
    $this->artisan('template:lint', ['slug' => 'soft-rose'])->expectsOutputToContain('soft-rose: no errors')->assertSuccessful();

    $this->artisan('template:lint', ['slug' => 'ghost'])->expectsOutputToContain('Unknown template [ghost].')->assertFailed();
});

it('reports a broken manifest instead of linting around it', function () {
    ($this->template)(['tier' => 'gold']);

    $this->artisan('template:lint')->expectsOutputToContain('tier must be one of')->assertFailed();
});
