<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/about', 'static.about');
Route::view('/help', 'static.help');
Route::view('/privacy', 'static.privacy');
