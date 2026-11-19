<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\TemplateRegistry;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;
use Tests\Support\TemplateFixtures;

beforeEach(function () {
    $this->withoutVite();
    $this->root = TemplateFixtures::tempRoot();
    TemplateFixtures::useRoot($this->root, withShared: true);
});

afterEach(fn () => TemplateFixtures::cleanup($this->root));

it('creates a complete template folder that the registry accepts', function () {
    $this->artisan('template:make', ['slug' => 'soft-rose', '--name' => 'Soft Rose', '--events' => 'wedding,engagement'])
        ->expectsOutputToContain('Template [soft-rose] created.')->assertSuccessful();

    foreach (['manifest.json', 'index.blade.php', 'theme.css', 'sections/.gitkeep', 'assets/.gitkeep'] as $file) {
        expect(is_file("{$this->root}/soft-rose/{$file}"))->toBeTrue("missing {$file}");
    }

    $manifest = app(TemplateRegistry::class)->get('soft-rose');
    expect($manifest->name)->toBe('Soft Rose')->and($manifest->events)->toBe([EventType::Wedding, EventType::Engagement])
        ->and($manifest->tier)->toBe('free')->and($manifest->paletteKeys())->toBe(['default'])
        ->and(file_get_contents("{$this->root}/soft-rose/theme.css"))->toContain("[data-theme='soft-rose']")->not->toMatch('/#[0-9a-fA-F]{3,6}\b/');
});

it('gives a new template a working preview and a valid cache straight away', function () {
    $this->artisan('template:make', ['slug' => 'soft-rose'])->assertSuccessful();

    $html = $this->get(Surfaces::url('public', 'preview/soft-rose/frame'))->assertOk()->getContent();
    $xp = DesignSystem::xpath($html);

    expect($html)->toContain('data-theme="soft-rose"')->and($xp->query('//h1')->length)->toBe(1)
        ->and($xp->query('//section[@id="people"]')->length)->toBe(1);

    $this->artisan('templates:cache')->expectsOutputToContain('Templates cached (1)')->assertSuccessful();
});

it('refuses a bad slug, an existing template, an unknown event type and an unknown tier', function (array $args, string $message) {
    $this->artisan('template:make', ['slug' => 'taken'])->assertSuccessful();

    $this->artisan('template:make', $args)->expectsOutputToContain($message)->assertFailed();
})->with([
    'upper case' => [['slug' => 'Soft_Rose'], 'The slug must be kebab-case'],
    'path traversal' => [['slug' => '../evil'], 'The slug must be kebab-case'],
    'leading underscore' => [['slug' => '_shared'], 'The slug must be kebab-case'],
    'existing' => [['slug' => 'taken'], 'Template [taken] already exists.'],
    'bad event' => [['slug' => 'ok-slug', '--events' => 'funeral'], 'Unknown event type [funeral]'],
    'bad tier' => [['slug' => 'ok-slug', '--tier' => 'gold'], 'Tier must be one of: free, premium.'],
]);

it('writes nothing outside its own folder when the slug is hostile', function () {
    $this->artisan('template:make', ['slug' => '../escape'])->assertFailed();

    expect(is_dir(dirname($this->root).'/escape'))->toBeFalse()->and(glob($this->root.'/*'))->toBe([]);
});
