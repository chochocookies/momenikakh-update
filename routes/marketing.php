<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Blade::render('<x-layouts.marketing>Marketing Surface</x-layouts.marketing>'))->name('marketing.home');

// DS-03: component styleguide (view: resources/views/styleguide.blade.php).
Route::get('/styleguide', fn () => view('styleguide'))->name('styleguide');
