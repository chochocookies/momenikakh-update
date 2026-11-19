<?php

declare(strict_types=1);

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\TemplateRegistry;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->url = fn (string $path = '') => Surfaces::url('public', "preview/classic-elegant{$path}");
});

it('shows the wrapper with a phone frame by default and never lets it be indexed', function () {
    $res = $this->get(($this->url)())->assertOk();

    $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect($res->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'none'");
    $res->assertHeader('X-Frame-Options', 'DENY');

    $xp = DesignSystem::xpath($res->getContent());
    expect($xp->query('//meta[@name="robots"]/@content')->item(0)->nodeValue)->toContain('noindex')
        ->and($xp->query('//iframe')->length)->toBe(1)
        ->and($xp->query('//iframe/@src')->item(0)->nodeValue)->toContain('/preview/classic-elegant/frame?event=wedding')
        ->and($xp->query('//iframe/@title')->item(0)->nodeValue)->toContain('Classic Elegant')
        ->and($xp->query('//iframe/@class')->item(0)->nodeValue)->toContain('w-[390px]')
        ->and($xp->query('//a[@aria-current="page"]')->length)->toBe(2); // one event type + one mode
});

it('switches between phone and desktop and between event types', function () {
    $desktop = $this->get(($this->url)().'?mode=desktop&event=engagement')->assertOk()->getContent();
    $xp = DesignSystem::xpath($desktop);

    expect($xp->query('//iframe/@class')->item(0)->nodeValue)->toContain('w-full')->not->toContain('w-[390px]')
        ->and($xp->query('//iframe/@src')->item(0)->nodeValue)->toContain('event=engagement')
        ->and($desktop)->toContain('Ponsel')->toContain('Desktop');
});

it('serves the frame so that only the same origin may embed it', function () {
    $res = $this->get(($this->url)('/frame'))->assertOk();

    $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $csp = (string) $res->headers->get('Content-Security-Policy');
    expect($csp)->toContain("frame-ancestors 'self'")->not->toContain("frame-ancestors 'none'")
        ->and(substr_count($csp, 'default-src'))->toBe(1)
        ->and($csp)->toContain("script-src 'self'");
    expect($res->getContent())->toContain('data-theme="classic-elegant"')->toContain('id="cover"')->toContain('data-guest');
});

it('renders one h1, unique ids and no inline script on the frame', function () {
    $xp = DesignSystem::xpath($this->get(($this->url)('/frame'))->getContent());

    $ids = [];
    foreach ($xp->query('//*[@id]') as $el) {
        $ids[] = $el->getAttribute('id');
    }
    expect($xp->query('//h1')->length)->toBe(1)
        ->and(array_keys(array_filter(array_count_values($ids), fn ($n) => $n > 1)))->toBe([])
        ->and($xp->query('//script[not(@src)]')->length)->toBe(0)
        ->and($xp->query('//html/@lang')->item(0)->nodeValue)->toBe('id');
});

it('answers 404 for an unknown template, an unknown event type or one the template does not support', function () {
    $this->get(Surfaces::url('public', 'preview/does-not-exist'))->assertNotFound();
    $this->get(Surfaces::url('public', 'preview/does-not-exist/frame'))->assertNotFound();
    $this->get(($this->url)('/frame').'?event=funeral')->assertNotFound();
    $this->get(($this->url)().'?event=funeral')->assertNotFound();
    $this->get(($this->url)('/frame').'?event=birthday')->assertNotFound(); // classic-elegant does not list birthday
});

it('ignores a hostile mode value instead of reflecting it', function () {
    $html = $this->get(($this->url)().'?mode='.urlencode('"><script>alert(1)</script>'))->assertOk()->getContent();

    expect($html)->not->toContain('<script>alert(1)')->and(DesignSystem::xpath($html)->query('//iframe/@class')->item(0)->nodeValue)->toContain('w-[390px]');
});

it('previews every template for every event type it supports', function () {
    $registry = app(TemplateRegistry::class);
    expect($registry->all())->not->toBeEmpty();

    foreach ($registry->all() as $slug => $manifest) {
        foreach ($manifest->events as $type) {
            $html = $this->get(Surfaces::url('public', "preview/{$slug}/frame?event={$type->value}"))->assertOk()->getContent();
            expect($html)->toContain("data-theme=\"{$slug}\"")->toContain('id="cover"')->toContain('id="people"');
            expect(EventType::from($type->value))->toBe($type);
        }
    }
});

it('keeps the preview route ahead of the invitation slug and reserves the slug', function () {
    expect(in_array('preview', config('reserved-slugs'), true))->toBeTrue();
});
