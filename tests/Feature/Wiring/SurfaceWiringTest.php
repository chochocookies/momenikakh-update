<?php

declare(strict_types=1);

use Tests\Support\WiringFixtures as Fx;

beforeEach(fn () => $this->withoutVite());

it('serves the styleguide on the marketing surface', function () {
    $this->get('http://'.config('surfaces.marketing.domain').'/styleguide')->assertOk()->assertSee('Styleguide', false);
});

it('renders the public surface with the public entry and the app surface with the dashboard entry', function () {
    $public = $this->get('http://'.config('surfaces.public.domain').'/')->assertOk();
    $public->assertSee('data-surface="public"', false);

    [$user] = Fx::owned();
    $this->actingAs($user)->get('http://'.config('surfaces.app.domain').'/dashboard')->assertOk();
});

it('keeps the guest-link and music scripts out of the dashboard bundle and the dashboard code out of the public bundle', function () {
    $public = (string) file_get_contents(base_path('resources/js/public.js'));
    $app = (string) file_get_contents(base_path('resources/js/app.js'));

    expect($public)->toContain('./public/guest-name')->toContain('./public/music')->not->toContain('ui-copy')
        ->and($app)->toContain('./ui-copy')->not->toContain('guest-name');
});
