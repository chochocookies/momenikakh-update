<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Momenikah - Marketing Surface', 200);
})->name('marketing.home');
