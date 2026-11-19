<?php

use App\Http\Controllers\Marketing\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing surface (momenikah.online): the indexed site
|--------------------------------------------------------------------------
| Auth and the dashboard live on the app surface; links to them use route('register'), route('login') and
| route('dashboard'), which resolve to that host. New marketing pages (MKT-02..05) are added below in this order:
| home, then catalogue, then per-event pages, then static pages.
*/

Route::get('/', HomeController::class)->name('marketing.home');

// DS-03: component styleguide (view: resources/views/styleguide/styleguide.blade.php). Never in production.
if (! app()->isProduction()) {
    Route::get('/styleguide', fn () => view('styleguide.styleguide'))->name('styleguide');
}
