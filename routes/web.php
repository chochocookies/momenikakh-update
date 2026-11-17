<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/styleguide', function () {
    return view('styleguide.index');
})->name('styleguide');

Route::get('/ds-02-verification', function () {
    return view('ds-02-verification');
})->name('ds-02-verification');
