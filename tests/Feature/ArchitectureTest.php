<?php

/**
 * Architecture Tests — Domain Isolation
 *
 * These tests enforce that the Domain layer (app/Domain) remains independent
 * from the Presentation layer (app/Http, app/Livewire, app/Filament).
 *
 * Requirements: 4.5, 4.6, 4.7, 4.8, 4.9, 4.10, 4.11, 4.12, 4.13, 4.14, 4.15
 */

// Requirement 4.15: app/Domain directory must exist
test('domain directory exists', function () {
    expect(is_dir(app_path('Domain')))->toBeTrue(
        'The app/Domain directory must exist for business logic isolation.'
    );
});

// Requirements 4.5, 4.6, 4.9, 4.13, 4.14
// Domain must not import from App\Http (HTTP controllers / presentation)
arch('Domain layer does not depend on App\\Http (HTTP presentation layer)')
    ->expect('App\\Domain')
    ->not->toUse('App\\Http');

// Requirements 4.5, 4.7, 4.9, 4.13, 4.14
// Domain must not import from App\Livewire (Livewire components / presentation)
arch('Domain layer does not depend on App\\Livewire (Livewire presentation layer)')
    ->expect('App\\Domain')
    ->not->toUse('App\\Livewire');

// Requirements 4.5, 4.8, 4.9, 4.13, 4.14
// Domain must not import from App\Filament (Filament admin resources / presentation)
arch('Domain layer does not depend on App\\Filament (Filament presentation layer)')
    ->expect('App\\Domain')
    ->not->toUse('App\\Filament');
