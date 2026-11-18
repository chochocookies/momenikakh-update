<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    View::share('errors', new ViewErrorBag);
});

dataset('layouts', [
    'marketing' => ['marketing', false],
    'app' => ['app', true],
    'public' => ['public', true],
]);

it('gives each layout a correct document skeleton', function (string $surface, bool $noindex) {
    $html = Blade::render("<x-layouts.{$surface} title=\"Uji\"><h1>Judul</h1></x-layouts.{$surface}>");
    $xp = DesignSystem::xpath($html);

    expect($xp->query('//html/@lang')->item(0)->nodeValue)->toBe('id');
    expect($xp->query('//body/@data-surface')->item(0)->nodeValue)->toBe($surface);
    expect($xp->query('//main[@id="main"]')->length)->toBe(1);
    expect($xp->query('//main')->length)->toBe(1);
    expect($xp->query('//title')->length)->toBe(1);

    $robots = $xp->query('//meta[@name="robots"]/@content');
    $noindex ? expect($robots->item(0)->nodeValue ?? '')->toContain('noindex') : expect($robots->length)->toBe(0);
})->with('layouts');

it('puts a skip link first and points it at main', function (string $surface) {
    $html = Blade::render("<x-layouts.{$surface}><p>x</p></x-layouts.{$surface}>");
    $xp = DesignSystem::xpath($html);
    $first = $xp->query('(//a[@href] | //button | //input)[1]')->item(0);
    expect($first->getAttribute('href'))->toBe('#main');
    expect($first->textContent)->toContain('konten');
})->with(['marketing', 'app', 'public']);

it('renders no inline script or style in layouts', function (string $surface) {
    $html = Blade::render("<x-layouts.{$surface}><p>x</p></x-layouts.{$surface}>");
    $xp = DesignSystem::xpath($html);
    expect($xp->query('//script[not(@src) and not(@type="application/ld+json")]')->length)->toBe(0);
    expect($xp->query('//style')->length)->toBe(0);
    expect($xp->query('//*[@style]')->length)->toBe(0);
})->with(['marketing', 'app', 'public']);

it('keeps chrome out of the public surface', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-layouts.public><p>Undangan</p></x-layouts.public>'));
    expect($xp->query('//header')->length)->toBe(0);
    expect($xp->query('//footer')->length)->toBe(0);
    expect($xp->query('//nav')->length)->toBe(0);
});

it('gives marketing a labelled landmark structure with the logo', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-layouts.marketing><p>x</p></x-layouts.marketing>'));
    expect($xp->query('//header')->length)->toBeGreaterThanOrEqual(1);
    expect($xp->query('//footer')->length)->toBe(1);
    $img = $xp->query('//header//img')->item(0);
    expect($img)->not->toBeNull();
    foreach (['alt', 'width', 'height'] as $attr) {
        expect($img->hasAttribute($attr))->toBeTrue("logo <img> lacks {$attr}");
    }
});

it('provides a polite live region for toasts on interactive surfaces', function (string $surface) {
    $xp = DesignSystem::xpath(Blade::render("<x-layouts.{$surface}><p>x</p></x-layouts.{$surface}>"));
    expect($xp->query('//*[@id="toasts" and @role="region"]')->length)->toBe(1);
})->with(['marketing', 'app']);

it('serves each surface with its own layout over the real routes', function (string $surface) {
    // Requires the test page from FND-05 at "/" on each surface. Adjust the path if different.
    $res = $this->get(Surfaces::url($surface, '/'));
    $res->assertSuccessful();
    $xp = DesignSystem::xpath($res->getContent());
    expect($xp->query('//body/@data-surface')->item(0)->nodeValue)->toBe($surface);
})->with(['marketing', 'app', 'public']);
