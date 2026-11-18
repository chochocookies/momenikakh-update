<?php

// 1) APP surface (routes/app.php): guest management and exports (GST-01..03)
use App\Http\Controllers\Dashboard\GuestController;
use App\Http\Controllers\Dashboard\GuestExportController;
use App\Http\Controllers\Dashboard\GuestImportController;
use Illuminate\Support\Facades\Route;

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

// 2) PUBLIC surface (routes/public.php): "guest opened" beacon. Add the path to the CSRF exceptions.
//    bootstrap/app.php -> ->withMiddleware(fn ($m) => $m->validateCsrfTokens(except: ['*/opened']))
use App\Http\Controllers\Api\GuestOpenedController;

Route::post('{slug}/opened', GuestOpenedController::class)
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:60,1')
    ->name('public.guest.opened');
// Declare it BEFORE the catch-all invitation route "/{slug}".
