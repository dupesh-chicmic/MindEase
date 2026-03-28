<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/privacy', 'static.privacy')->name('privacy');
Route::view('/help', 'static.help')->name('help');
Route::view('/about', 'static.about')->name('about');
