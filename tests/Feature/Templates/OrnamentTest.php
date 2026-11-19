<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\TemplateRenderer;
use App\Domain\Templates\TemplateSampleData;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    View::share('errors', new ViewErrorBag);
});

it('draws every ornament in currentColor so one file can take any theme colour', function () {
    $files = File::files(resource_path('svg/ornaments'));
    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $svg = $file->getContents();
        expect(str_contains($svg, 'currentColor'))->toBeTrue("{$file->getFilename()} must use currentColor");
        expect(preg_match('/(?:fill|stroke|stop-color)="(?!none|currentColor|url\()/', $svg))->toBe(0, "{$file->getFilename()} has a fixed colour");
        expect($svg)->not->toContain('<script')->not->toContain('onload')->not->toContain('<image')->not->toContain('xlink:href');
    }
});

it('renders an ornament as decorative inline svg in its layer', function () {
    $html = Blade::render('<x-ornament name="corner-flourish" layer="corner" corner="tr" />');
    $xp = DesignSystem::xpath($html);

    expect($xp->query('//span[@aria-hidden="true"]')->length)->toBe(1)
        ->and($xp->query('//span[contains(@class,"t-orn t-orn--corner")]')->length)->toBe(1)
        ->and($xp->query('//span/@data-corner')->item(0)->nodeValue)->toBe('tr')
        ->and($xp->query('//span/svg')->length)->toBe(1)
        ->and($xp->query('//a|//button|//input')->length)->toBe(0);
});

it('falls back to the divider layer for an unknown layer and ignores a stray corner', function () {
    $html = Blade::render('<x-ornament name="divider-diamond" layer="confetti" corner="tl" />');

    expect($html)->toContain('t-orn--divider')->not->toContain('data-corner');
});

it('never reads a file outside resources/svg/ornaments', function (string $name) {
    $html = Blade::render('<x-ornament :name="$name" />', ['name' => $name]);

    expect($html)->not->toContain('<svg')->not->toContain('APP_KEY')->toContain('aria-hidden="true"');
})->with(['../../../.env', '..\\..\\composer.json', 'does-not-exist', 'a/b', "x\0y"]);

it('keeps the layers in the fixed order and never lets an ornament take a click', function () {
    $css = (string) file_get_contents(resource_path('css/invitation-core.css'));

    expect($css)->toMatch('/\.t-orn\s*\{[^}]*pointer-events:\s*none/');
    $order = ['bg' => 0, 'pattern' => 1, 'frame' => 2, 'corner' => 3, 'divider' => 5, 'float' => 6];
    foreach ($order as $layer => $z) {
        expect($css)->toMatch('/\.t-orn--'.$layer.'\s*\{[^}]*z-index:\s*'.$z.'\s*;/');
    }
});

it('gives Classic Elegant a framed cover with the same flourish in four corners, at most 3 ornaments per section', function () {
    $html = view('templates::classic-elegant.index', app(TemplateRenderer::class)->data(
        app(TemplateSampleData::class)->viewModel(EventType::Wedding, 'classic-elegant'),
    ))->render();
    $xp = DesignSystem::xpath($html);

    $corners = [];
    foreach ($xp->query('//*[@id="cover"]//*[@data-orn="corner-flourish"]/@data-corner') as $c) {
        $corners[] = $c->nodeValue;
    }
    expect($corners)->toEqualCanonicalizing(['tl', 'tr', 'bl', 'br'])
        ->and($xp->query('//*[@id="cover"]//*[@data-orn="frame-double"]')->length)->toBe(1);

    foreach ($xp->query('//section[@id]') as $section) {
        $distinct = [];
        foreach ($xp->query('.//*[@data-orn]', $section) as $orn) {
            $distinct[$orn->getAttribute('data-orn')] = true;
        }
        expect(count($distinct))->toBeLessThanOrEqual(3, "{$section->getAttribute('id')} uses too many ornaments");
    }
});
