<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Momenikah - Application Surface', 200);
})->name('app.home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
