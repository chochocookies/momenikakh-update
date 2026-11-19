<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\GuestController;
use App\Http\Controllers\Dashboard\GuestExportController;
use App\Http\Controllers\Dashboard\GuestImportController;
use App\Http\Controllers\Dashboard\InvitationController;
use App\Http\Controllers\Dashboard\MediaUploadController;
use App\Http\Controllers\Dashboard\TemplateAssetController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Blade::render('<x-layouts.app>Application Surface</x-layouts.app>'))->name('app.home');

// DSH-01/02: the owner's invitations and the create wizard. "create" is declared before "{invitation}" on purpose.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/invitations/create', [InvitationController::class, 'create'])->name('dashboard.invitations.create');
    Route::post('/dashboard/invitations', [InvitationController::class, 'store'])->middleware('throttle:20,1')->name('dashboard.invitations.store');
    Route::get('/dashboard/invitations/{invitation}', [InvitationController::class, 'show'])->whereNumber('invitation')->name('dashboard.invitations.show');

    // thumb.webp / preview.webp of a template, for the template cards (the CSP of this surface only allows its own images).
    Route::get('/dashboard/templates/{template}/{file}', TemplateAssetController::class)
        ->where(['template' => '[a-z0-9]+(?:-[a-z0-9]+)*', 'file' => 'thumb\.webp|preview\.webp'])
        ->name('dashboard.template.image');
});

// GST-01..03: guest list, CSV import (preview -> confirm), per-guest links, RSVP/message exports.
Route::middleware(['auth', 'verified'])
    ->prefix('dashboard/invitations/{invitation}')
    ->scopeBindings()
    ->name('dashboard.guests.')
    ->group(function () {
        Route::get('guests', [GuestController::class, 'index'])->name('index');
        Route::post('guests', [GuestController::class, 'store'])->middleware('throttle:60,1')->name('store');
        Route::get('guests/template.csv', [GuestImportController::class, 'template'])->name('template');
        Route::post('guests/import', [GuestImportController::class, 'preview'])->middleware('throttle:10,1')->name('import.preview');
        Route::post('guests/import/{import}', [GuestImportController::class, 'commit'])->middleware('throttle:10,1')->name('import.commit');
        Route::delete('guests/import/{import}', [GuestImportController::class, 'cancel'])->name('import.cancel');
        Route::delete('guests/{guest}', [GuestController::class, 'destroy'])->name('destroy');
        Route::post('guests/{guest}/token', [GuestController::class, 'regenerateToken'])->name('token');

        Route::get('export/rsvps.csv', [GuestExportController::class, 'rsvps'])->middleware('throttle:20,1')->name('export.rsvps');
        Route::get('export/messages.csv', [GuestExportController::class, 'messages'])->middleware('throttle:20,1')->name('export.messages');
    });

// MED-01..05: image and audio uploads. {collection} is cover | gallery | music on the invitation itself.
Route::middleware(['auth', 'verified'])
    ->prefix('dashboard/invitations/{invitation}')
    ->scopeBindings()
    ->name('dashboard.media.')
    ->group(function () {
        Route::post('media/{collection}', [MediaUploadController::class, 'store'])
            ->whereIn('collection', ['cover', 'gallery', 'music'])->middleware('throttle:30,1')->name('store');
        Route::put('media/gallery/order', [MediaUploadController::class, 'reorderGallery'])->name('reorder');
        Route::delete('media/{media}', [MediaUploadController::class, 'destroy'])->whereNumber('media')->name('destroy');
        Route::post('people/{person}/photo', [MediaUploadController::class, 'storePersonPhoto'])
            ->defaults('collection', 'photo')->middleware('throttle:30,1')->name('person-photo');
        Route::post('gifts/{gift}/qr', [MediaUploadController::class, 'storeGiftQr'])
            ->defaults('collection', 'qr')->middleware('throttle:30,1')->name('gift-qr');
    });
