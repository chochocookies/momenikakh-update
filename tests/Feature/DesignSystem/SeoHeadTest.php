<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Tests\Support\DesignSystem;

beforeEach(fn () => $this->withoutVite());

it('renders the essential head tags', function () {
    $html = Blade::render('<x-seo.head title="Katalog Template" description="Pilih template undangan digital." />');
    $xp = DesignSystem::xpath($html);

    expect($xp->query('//title')->item(0)->textContent)->toBe('Katalog Template | Momenikah');
    expect($xp->query('//meta[@name="description"]/@content')->item(0)->nodeValue)->toBe('Pilih template undangan digital.');
    expect($xp->query('//link[@rel="canonical"]/@href')->item(0)->nodeValue)->toStartWith('http');
    expect($xp->query('//meta[@name="viewport"]/@content')->item(0)->nodeValue)->not->toContain('user-scalable=no')->not->toContain('maximum-scale');
    foreach (['og:title', 'og:description', 'og:url', 'og:image', 'og:type', 'og:locale', 'og:site_name'] as $p) {
        expect($xp->query('//meta[@property="'.$p.'"]')->length)->toBe(1, $p);
    }
    expect($xp->query('//meta[@name="twitter:card"]/@content')->item(0)->nodeValue)->toBe('summary_large_image');
    expect($xp->query('//meta[@name="theme-color"]')->length)->toBe(1);
    expect($xp->query('//link[@rel="manifest"]')->length)->toBe(1);
    expect($xp->query('//link[@rel="apple-touch-icon"]')->length)->toBe(1);
    expect($xp->query('//link[@rel="icon" and @type="image/svg+xml"]')->length)->toBe(1);
});

it('uses a sensible default title and keeps titles short', function () {
    $html = Blade::render('<x-seo.head />');
    $title = DesignSystem::xpath($html)->query('//title')->item(0)->textContent;
    expect($title)->toContain('Momenikah')->and(mb_strlen($title))->toBeLessThanOrEqual(60);
});

it('adds noindex only when asked', function () {
    $on = DesignSystem::xpath(Blade::render('<x-seo.head :noindex="true" />'));
    expect($on->query('//meta[@name="robots"]/@content')->item(0)->nodeValue)->toContain('noindex');
    $off = DesignSystem::xpath(Blade::render('<x-seo.head />'));
    expect($off->query('//meta[@name="robots"]')->length)->toBe(0);
});

it('escapes user supplied text', function () {
    $html = Blade::render('<x-seo.head title=\'"><script>alert(1)</script>\' />');
    expect($html)->not->toContain('<script>alert(1)</script>');
});

it('ships every brand file at the public root with the right size', function () {
    $sizes = [
        'favicon-16x16.png' => [16, 16], 'favicon-32x32.png' => [32, 32], 'apple-touch-icon.png' => [180, 180],
        'icon-192.png' => [192, 192], 'icon-512.png' => [512, 512], 'icon-maskable-512.png' => [512, 512],
        'og-default.png' => [1200, 630],
    ];
    foreach ($sizes as $file => [$w, $h]) {
        $path = public_path($file);
        expect(is_file($path))->toBeTrue("missing public/{$file}");
        $info = getimagesize($path);
        expect([$info[0], $info[1]])->toBe([$w, $h], $file);
    }
    foreach (['favicon.svg', 'site.webmanifest', 'favicon.ico'] as $file) {
        expect(is_file(public_path($file)))->toBeTrue("missing public/{$file}");
    }
    expect(bin2hex(substr((string) file_get_contents(public_path('favicon.ico')), 0, 4)))->toBe('00000100');
    expect(@simplexml_load_file(public_path('favicon.svg')))->not->toBeFalse();
});

it('has a valid web app manifest whose icons exist', function () {
    $m = json_decode((string) file_get_contents(public_path('site.webmanifest')), true, 512, JSON_THROW_ON_ERROR);
    foreach (['name', 'short_name', 'start_url', 'display', 'theme_color', 'background_color', 'icons', 'lang'] as $key) {
        expect($m)->toHaveKey($key);
    }
    expect($m['lang'])->toBe('id');
    $purposes = [];
    foreach ($m['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));
        expect(is_file($path))->toBeTrue("manifest icon missing: {$icon['src']}");
        [$w, $h] = explode('x', $icon['sizes']);
        $info = getimagesize($path);
        expect([$info[0], $info[1]])->toBe([(int) $w, (int) $h]);
        $purposes[] = $icon['purpose'] ?? 'any';
    }
    expect($purposes)->toContain('maskable')->toContain('any');
});

it('matches the manifest theme colour to the brand rose', function () {
    $m = json_decode((string) file_get_contents(public_path('site.webmanifest')), true);
    expect(strtoupper($m['theme_color']))->toBe(strtoupper(DesignSystem::tokens()['--color-brand-rose']));
});

it('ships the logo files used by the layouts', function () {
    foreach (['momenikah-logo-horizontal.svg', 'momenikah-logo-horizontal-reverse.svg', 'momenikah-icon.svg'] as $f) {
        expect(is_file(public_path("brand/{$f}")))->toBeTrue("missing public/brand/{$f}");
    }
});
