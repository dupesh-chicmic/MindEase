<?php

use App\Http\Controllers\Api\V1\SuggestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/suggestions', [SuggestionController::class, 'index']);
});
