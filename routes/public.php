<?php

use App\Http\Controllers\Api\GuestActionController;
use App\Http\Controllers\Api\GuestOpenedController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Share\InvitationController;
use App\Http\Controllers\Share\PreviewController;
use App\Http\Middleware\ForceJson;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Blade::render('<x-layouts.public>Public Invitation Surface</x-layouts.public>'))->name('public.home');

// PUB-04: template preview with fictional sample data. Keep BEFORE the catch-all "/{slug}" of PUB-01 ("preview" is a reserved slug).
Route::prefix('preview/{template}')->where(['template' => '[a-z0-9]+(?:-[a-z0-9]+)*'])->middleware('throttle:120,1')->group(function () {
    Route::get('/', [PreviewController::class, 'show'])->middleware('preview.headers')->name('public.preview');
    Route::get('frame', [PreviewController::class, 'frame'])->middleware('preview.headers:frame')->name('public.preview.frame');
});

// GST-02: "guest opened" beacon (navigator.sendBeacon). Always 204. CSRF-exempt in bootstrap/app.php because the
// invitation page is cached at the edge and cannot embed a token. Keep this BEFORE the catch-all "/{slug}" route
// that the invitation route at the bottom of this file adds.
Route::post('{slug}/opened', GuestOpenedController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:60,1')
    ->name('public.guest.opened');

// PUB-06: "seen" beacon (view, open). Always 204. Declared before the catch-all like the opened beacon.
Route::post('{slug}/seen', VisitController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:60,1')
    ->name('public.seen');

// PUB-09 / PUB-10: RSVP and wishes. JSON only, no CSRF (stateless surface); protected by throttle, honeypot and Turnstile.
Route::prefix('{slug}')->where(['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'])->middleware(ForceJson::class)->group(function () {
    Route::post('rsvp', [GuestActionController::class, 'rsvp'])->middleware('throttle:10,1')->name('public.rsvp');
    Route::get('wishes', [GuestActionController::class, 'wishes'])->middleware('throttle:60,1')->name('public.wishes');
    Route::post('wishes', [GuestActionController::class, 'storeWish'])->middleware('throttle:10,1')->name('public.wishes.store');
});

// PUB-05: slug alias redirect — keep BEFORE the main invitation route so old slugs redirect to the canonical one.
Route::get('{slug}', [InvitationController::class, 'redirectAlias'])
    ->where('slug', '(?!(?:'.implode('|', array_map('preg_quote', (array) config('reserved-slugs'))).')$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:240,1')
    ->name('public.invitation.alias');

// PUB-01: the invitation itself. This is the catch-all of the surface, so it stays LAST. Reserved slugs (preview, health,
// up, admin ...) never match it, which keeps /health and /up answering on this host too.
Route::get('{slug}', [InvitationController::class, 'show'])
    ->where('slug', '(?!(?:'.implode('|', array_map('preg_quote', (array) config('reserved-slugs'))).')$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:240,1')
    ->name('public.invitation');
