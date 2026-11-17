<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    View::share('errors', new ViewErrorBag());
});

it('has no emoji in views', function () {
    $hits = [];
    foreach (DesignSystem::views() as $f) {
        if (preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $f['content'])) {
            $hits[] = $f['rel'];
        }
    }
    expect($hits)->toBeEmpty();
});

it('uses only icons that exist', function () {
    $missing = [];
    foreach (DesignSystem::views() as $f) {
        preg_match_all('/<x-ui\.icon[^>]*\sname="([a-z0-9-]+)"/', $f['content'], $m);
        foreach ($m[1] as $name) {
            if (! is_file(resource_path("svg/icons/{$name}.svg"))) {
                $missing[] = "{$f['rel']}: {$name}";
            }
        }
    }
    expect($missing)->toBeEmpty();
});

it('ships only clean, sizeable, recolourable svg icons', function () {
    $problems = [];
    foreach (File::files(resource_path('svg/icons')) as $file) {
        $svg = $file->getContents();
        $name = $file->getFilename();
        if (! preg_match('/viewBox="0 0 24 24"/', $svg)) {
            $problems[] = "{$name}: missing viewBox 0 0 24 24";
        }
        if (preg_match('/<svg[^>]*\s(width|height)=/', $svg)) {
            $problems[] = "{$name}: fixed width/height (use size classes)";
        }
        if (preg_match('/<script|on\w+=|javascript:|<foreignObject/i', $svg)) {
            $problems[] = "{$name}: unsafe markup";
        }
        if (! str_contains($svg, 'currentColor')) {
            $problems[] = "{$name}: not recolourable (no currentColor)";
        }
        if (@simplexml_load_string($svg) === false) {
            $problems[] = "{$name}: malformed XML";
        }
    }
    expect($problems)->toBeEmpty();
});

it('hides decorative icons and labels meaningful ones', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-ui.icon name="check" /><x-ui.icon name="check" label="Berhasil" />'));
    expect($xp->query('//span[@aria-hidden="true"]')->length)->toBe(1);
    expect($xp->query('//span[@role="img" and @aria-label="Berhasil"]')->length)->toBe(1);
});

it('cannot be used to read arbitrary files', function () {
    foreach (['../../.env', '..%2F..%2F.env', 'check/../../../.env', 'CHECK', 'check.svg'] as $evil) {
        $html = Blade::render('<x-ui.icon name="'.$evil.'" />');
        expect($html)->not->toContain('APP_KEY')->not->toContain('DB_PASSWORD')->not->toContain('<svg');
    }
});
