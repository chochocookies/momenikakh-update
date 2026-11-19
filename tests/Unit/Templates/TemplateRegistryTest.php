<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\Exceptions\InvalidTemplateManifest;
use App\Domain\Templates\TemplateManifest;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Support\Facades\File;
use Tests\Support\TemplateFixtures;

beforeEach(function () {
    $this->root = TemplateFixtures::tempRoot();
    // Outside the templates folder, like bootstrap/cache in the real app (inside it, the cache dir would be scanned).
    $this->cacheFile = $this->root.'-cache'.DIRECTORY_SEPARATOR.'templates.php';
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('discovers templates from their manifest and skips _shared and hidden folders', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['event_types' => ['wedding', 'engagement']]));
    TemplateFixtures::make($this->root, 'dark-oak', TemplateFixtures::manifest('dark-oak', ['name' => 'Dark Oak', 'tier' => 'premium']));
    TemplateFixtures::make($this->root, '_shared', null, false);
    TemplateFixtures::make($this->root, '.draft', null, false);

    $registry = new TemplateRegistry($this->root);

    expect($registry->slugs())->toBe(['dark-oak', 'soft-rose'])
        ->and($registry->get('dark-oak')->isPremium())->toBeTrue()
        ->and($registry->get('soft-rose')->events)->toBe([EventType::Wedding, EventType::Engagement])
        ->and($registry->has('_shared'))->toBeFalse()
        ->and($registry->find('nope'))->toBeNull();
});

it('lists the templates that support an event type', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['event_types' => ['wedding', 'engagement']]));
    TemplateFixtures::make($this->root, 'kids', TemplateFixtures::manifest('kids', ['event_types' => ['khitanan']]));

    $registry = new TemplateRegistry($this->root);

    expect(array_keys($registry->forEvent(EventType::Khitanan)))->toBe(['kids'])
        ->and(array_keys($registry->forEvent(EventType::Wedding)))->toBe(['soft-rose']);
});

it('rejects a broken manifest with a message that names the folder and the problem', function (array $override, string $expected) {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', $override));

    expect(fn () => (new TemplateRegistry($this->root))->all())
        ->toThrow(InvalidTemplateManifest::class, "resources/templates/soft-rose: {$expected}");
})->with([
    'slug differs from folder' => [['slug' => 'other'], 'slug [other] must equal the folder name [soft-rose].'],
    'bad version' => [['version' => 'v1'], 'version must look like 1.0.0.'],
    'unknown tier' => [['tier' => 'gold'], 'tier must be one of: free, premium.'],
    'unknown event type' => [['event_types' => ['wedding', 'funeral']], 'event_types contains unknown value [funeral]'],
    'unknown section' => [['sections' => ['cover', 'confetti']], 'sections contains unknown value [confetti]'],
    'missing cover' => [['sections' => ['opening']], 'sections must include [cover].'],
    'duplicate section' => [['sections' => ['cover', 'cover']], 'sections contains duplicates.'],
    'bad palette colour' => [['palettes' => [['key' => 'a', 'name' => 'A', 'tokens' => ['--t-bg' => 'red', '--t-ink' => '#000', '--t-accent' => '#fff', '--t-orn' => '#fff']]]], 'palettes.a.tokens.--t-bg must be a hex colour'],
    'palette misses a required token' => [['palettes' => [['key' => 'a', 'name' => 'A', 'tokens' => ['--t-bg' => '#fff']]]], 'palettes.a.tokens.--t-ink must be a hex colour'],
    'duplicate palette keys' => [['palettes' => [
        ['key' => 'a', 'name' => 'A', 'tokens' => ['--t-bg' => '#fff', '--t-ink' => '#000', '--t-accent' => '#111', '--t-orn' => '#222']],
        ['key' => 'a', 'name' => 'B', 'tokens' => ['--t-bg' => '#fff', '--t-ink' => '#000', '--t-accent' => '#111', '--t-orn' => '#222']],
    ]], 'palettes contains duplicate keys.'],
    'no palette at all' => [['palettes' => []], 'palettes must be a non-empty list.'],
    'motion is not an object' => [['motion' => 'wild'], 'motion must be an object'],
    'budget too large' => [['budget' => ['css_kb' => 99999, 'js_kb' => 1, 'assets_kb' => 1]], 'budget.css_kb must be an integer between 1 and 2000.'],
    'budget key missing' => [['budget' => ['css_kb' => 30]], 'budget.js_kb must be an integer between 1 and 2000.'],
]);

it('accepts the older section names of TEMPLATE_SPEC and stores the normalized keys', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['sections' => ['cover', 'couple', 'wishes', 'gifts']]));

    expect((new TemplateRegistry($this->root))->get('soft-rose')->sections)->toBe(['cover', 'people', 'guestbook', 'gift']);
});

it('knows its palettes and falls back to the first one', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose'));

    $manifest = (new TemplateRegistry($this->root))->get('soft-rose');

    expect($manifest->paletteKeys())->toBe(['rose'])->and($manifest->defaultPalette())->toBe('rose');
});

it('reports a missing key, a missing manifest and missing files together', function () {
    $broken = TemplateFixtures::manifest('no-name');
    unset($broken['name']);
    TemplateFixtures::make($this->root, 'no-name', $broken);
    TemplateFixtures::make($this->root, 'no-manifest', null, false);
    TemplateFixtures::make($this->root, 'no-files', TemplateFixtures::manifest('no-files'), false);

    try {
        (new TemplateRegistry($this->root))->all();
        $this->fail('Expected InvalidTemplateManifest.');
    } catch (InvalidTemplateManifest $e) {
        expect($e->getMessage())
            ->toContain('resources/templates/no-name: missing key [name].')
            ->toContain('resources/templates/no-manifest: manifest.json not found.')
            ->toContain('resources/templates/no-files: index.blade.php not found.')
            ->toContain('resources/templates/no-files: theme.css not found.');
    }
});

it('rejects a manifest that does not return an array', function () {
    File::ensureDirectoryExists($this->root.'/oops');
    file_put_contents($this->root.'/oops/manifest.json', '"nope"');

    expect(fn () => (new TemplateRegistry($this->root))->all())->toThrow(InvalidTemplateManifest::class, 'manifest.json must contain a JSON object.');
});

it('caches the list and serves it without scanning the folders', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose'));

    $registry = new TemplateRegistry($this->root, $this->cacheFile);
    expect($registry->isCached())->toBeFalse()->and($registry->cache())->toBe(1)->and($registry->isCached())->toBeTrue();

    File::deleteDirectory($this->root.'/soft-rose'); // folder gone, cache still answers
    $fresh = new TemplateRegistry($this->root, $this->cacheFile);
    expect($fresh->slugs())->toBe(['soft-rose'])->and($fresh->get('soft-rose'))->toBeInstanceOf(TemplateManifest::class);

    expect($fresh->clearCache())->toBeTrue()->and((new TemplateRegistry($this->root, $this->cacheFile))->slugs())->toBe([]);
});

it('does not write the cache when a manifest is invalid', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['tier' => 'gold']));

    expect(fn () => (new TemplateRegistry($this->root, $this->cacheFile))->cache())->toThrow(InvalidTemplateManifest::class)
        ->and(is_file($this->cacheFile))->toBeFalse();
});

it('round-trips a manifest through toArray', function () {
    $manifest = TemplateManifest::fromArray(TemplateFixtures::manifest(), 'soft-rose');

    expect(TemplateManifest::fromArray($manifest->toArray(), 'soft-rose'))->toEqual($manifest);
});

it('throws for an unknown template slug', function () {
    expect(fn () => (new TemplateRegistry($this->root))->get('ghost'))->toThrow(InvalidArgumentException::class, 'Unknown template [ghost].');
});

it('has templates:cache and templates:clear commands that report a bad manifest', function () {
    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose', ['version' => 'x']));
    app()->instance(TemplateRegistry::class, new TemplateRegistry($this->root, $this->cacheFile));

    $this->artisan('templates:cache')->expectsOutputToContain('version must look like 1.0.0.')->assertFailed();

    TemplateFixtures::make($this->root, 'soft-rose', TemplateFixtures::manifest('soft-rose'));
    $this->artisan('templates:cache')->expectsOutputToContain('Templates cached (1)')->assertSuccessful();
    expect(is_file($this->cacheFile))->toBeTrue();

    $this->artisan('templates:clear')->assertSuccessful();
    expect(is_file($this->cacheFile))->toBeFalse();
});
