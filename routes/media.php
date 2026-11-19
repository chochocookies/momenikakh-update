<?php

// APP surface (routes/app.php): media uploads (MED-01..05)
use App\Http\Controllers\Dashboard\MediaUploadController;
use Illuminate\Support\Facades\Route;

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

// routes/console.php (MED-04): daily cleanup. Run `php artisan media:prune-orphans --dry-run` for the first week.
use Illuminate\Support\Facades\Schedule;

Schedule::command('media:prune-orphans')->dailyAt('03:15')->withoutOverlapping()->onOneServer();
