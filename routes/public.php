<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Momenikah - Public Invitation Surface', 200);
})->name('public.home');
