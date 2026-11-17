<?php

// Multi-Surface Routing Tests
//
// Verifies that domain-based routing correctly dispatches requests to the
// marketing, app, and public surfaces, that each response carries the
// X-Surface identifier header, and that unrecognised domains receive 404.
//
// Run with: php artisan test --group=routing
// These tests are opt-in and excluded from the default suite.
//
// Requirements: 5.1, 5.2, 5.3, 5.4, 5.5

test('marketing domain returns 200 with marketing content within 2 seconds', function () {
    $startTime = microtime(true);

    $response = $this->get('http://momenikah.test/');

    $elapsed = microtime(true) - $startTime;

    $response->assertStatus(200)
        ->assertSee('Marketing Surface');

    expect($elapsed)->toBeLessThan(2.0);
})->group('routing');

test('marketing domain response includes X-Surface: marketing header', function () {
    $response = $this->get('http://momenikah.test/');

    $response->assertStatus(200)
        ->assertHeader('X-Surface', 'marketing');
})->group('routing');

test('app subdomain returns 200 with app content within 2 seconds', function () {
    $startTime = microtime(true);

    $response = $this->get('http://app.momenikah.test/');

    $elapsed = microtime(true) - $startTime;

    $response->assertStatus(200)
        ->assertSee('Application Surface');

    expect($elapsed)->toBeLessThan(2.0);
})->group('routing');

test('app subdomain response includes X-Surface: app header', function () {
    $response = $this->get('http://app.momenikah.test/');

    $response->assertStatus(200)
        ->assertHeader('X-Surface', 'app');
})->group('routing');

test('public subdomain returns 200 with public content within 2 seconds', function () {
    $startTime = microtime(true);

    $response = $this->get('http://undangan.momenikah.test/');

    $elapsed = microtime(true) - $startTime;

    $response->assertStatus(200)
        ->assertSee('Public Invitation Surface');

    expect($elapsed)->toBeLessThan(2.0);
})->group('routing');

test('public subdomain response includes X-Surface: public header', function () {
    $response = $this->get('http://undangan.momenikah.test/');

    $response->assertStatus(200)
        ->assertHeader('X-Surface', 'public');
})->group('routing');

test('unrecognized domain returns HTTP 404', function () {
    $response = $this->get('http://unknown.momenikah.test/');

    $response->assertStatus(404);
})->group('routing');
