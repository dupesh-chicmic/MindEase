<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ProfileController;
use App\Http\Controllers\API\Chat\ChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::post('forgot-password', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('update-password', [ForgotPasswordController::class, 'updatePassword']);

    Route::middleware('session.token')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile/update', [ProfileController::class, 'update']);
    });
});

Route::prefix('v1/chat')->middleware('session.token')->group(function () {
    Route::post('thread', [ChatController::class, 'createThread']);
    Route::post('send', [ChatController::class, 'sendMessage']);
    Route::get('history', [ChatController::class, 'getHistory']);
    Route::get('threads', [ChatController::class, 'getThreads']);
    Route::delete('thread/{id}', [ChatController::class, 'deleteThread']);
});
