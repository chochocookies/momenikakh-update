<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('adds global security headers to responses on marketing surface', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Content-Security-Policy');
});

it('adds global security headers to responses on app surface', function () {
    $response = $this->withDomain('app.momenikah.test')->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Content-Security-Policy');
});

it('adds global security headers to responses on public surface', function () {
    $response = $this->withDomain('undangan.momenikah.test')->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Content-Security-Policy');
});

it('does not duplicate security headers on app surface', function () {
    $response = $this->withDomain('app.momenikah.test')->get('/');

    $cspHeader = $response->headers->get('Content-Security-Policy');
    $headerCount = substr_count($cspHeader, 'default-src');

    expect($headerCount)->toBe(1);
});

it('does not duplicate security headers on public surface', function () {
    $response = $this->withDomain('undangan.momenikah.test')->get('/');

    $cspHeader = $response->headers->get('Content-Security-Policy');
    $headerCount = substr_count($cspHeader, 'default-src');

    expect($headerCount)->toBe(1);
});
