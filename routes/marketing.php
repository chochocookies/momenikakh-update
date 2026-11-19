<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Blade::render('<x-layouts.marketing>Marketing Surface</x-layouts.marketing>'))->name('marketing.home');

// DS-03: component styleguide (view: resources/views/styleguide/styleguide.blade.php). Never in production.
if (! app()->isProduction()) {
    Route::get('/styleguide', fn () => view('styleguide.styleguide'))->name('styleguide');
}
