<?php

use App\Http\Controllers\Api\GuestOpenedController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Blade::render('<x-layouts.public>Public Invitation Surface</x-layouts.public>'))->name('public.home');

// GST-02: "guest opened" beacon (navigator.sendBeacon). Always 204. CSRF-exempt in bootstrap/app.php because the
// invitation page is cached at the edge and cannot embed a token. Keep this BEFORE the catch-all "/{slug}" route
// that the public invitation task (PUB-01) will add.
Route::post('{slug}/opened', GuestOpenedController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:60,1')
    ->name('public.guest.opened');
