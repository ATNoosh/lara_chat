<?php

use Illuminate\Support\Facades\Route;

// Embedded chat route
Route::get('/embedded', function () {
    return view('app');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
