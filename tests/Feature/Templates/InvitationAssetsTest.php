<?php

declare(strict_types=1);

use App\Domain\Templates\TemplateRegistry;
use Tests\Support\Surfaces;

/** These tests read the real Vite build (npm run build runs before the test suite in CI and in the gate). */
function viteManifest(): ?array
{
    $file = public_path('build/manifest.json');

    return is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
}

it('lists exactly the shared core and one theme for a template', function () {
    $manifest = app(TemplateRegistry::class)->get('classic-elegant');

    expect($manifest->viteEntries())->toBe([
        'resources/css/invitation-core.css',
        'resources/js/invitation/core.js',
        'resources/templates/classic-elegant/theme.css',
    ]);
});

it('builds a theme entry for every template folder automatically', function () {
    $vite = viteManifest() ?? $this->markTestSkipped('Run npm run build first.');

    foreach (app(TemplateRegistry::class)->all() as $manifest) {
        foreach ($manifest->viteEntries() as $entry) {
            expect(array_key_exists($entry, $vite))->toBeTrue("{$entry} is not in the Vite manifest");
        }
        if (is_file(resource_path("templates/{$manifest->slug}/theme.js"))) {
            expect(array_key_exists("resources/templates/{$manifest->slug}/theme.js", $vite))->toBeTrue();
        }
    }
});

it('loads only core and one theme on an invitation page, not the dashboard bundle', function () {
    $vite = viteManifest() ?? $this->markTestSkipped('Run npm run build first.');

    $html = $this->get(Surfaces::url('public', 'preview/classic-elegant/frame'))->assertOk()->getContent();

    foreach (['resources/css/invitation-core.css', 'resources/templates/classic-elegant/theme.css'] as $css) {
        expect($html)->toContain($vite[$css]['file']);
    }
    expect($html)->toContain($vite['resources/js/invitation/core.js']['file'])
        ->not->toContain($vite['resources/css/app.css']['file'])
        ->not->toContain($vite['resources/js/app.js']['file']);
});

it('keeps the invitation core small: 8 KB gzip or less, no jQuery', function () {
    $vite = viteManifest() ?? $this->markTestSkipped('Run npm run build first.');

    $entry = $vite['resources/js/invitation/core.js'];
    $files = [$entry['file'], ...array_map(fn (string $key) => $vite[$key]['file'], $entry['imports'] ?? [])];
    $bytes = 0;
    foreach ($files as $file) {
        $source = (string) file_get_contents(public_path("build/{$file}"));
        $bytes += strlen((string) gzencode($source, 9));
        expect($source)->not->toContain('jQuery');
    }

    expect($bytes)->toBeLessThanOrEqual(8 * 1024);
});
