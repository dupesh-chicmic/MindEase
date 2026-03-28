<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ProfileController;
use App\Http\Controllers\API\CalendarController;
use App\Http\Controllers\API\Chat\ChatController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Fcm\FcmController;
use App\Http\Controllers\API\Insight\InsightController;
use App\Http\Controllers\Api\V1\MoodController as V1UserMoodController;
use App\Http\Controllers\Api\V1\SuggestionController;
use App\Http\Controllers\API\MoodController;
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

Route::prefix('v1')->middleware('session.token')->group(function () {
    Route::get('calendar', [CalendarController::class, 'index']);
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('insights', [InsightController::class, 'index']);
    Route::get('suggestions', [SuggestionController::class, 'index']);
    Route::post('mood/log', [MoodController::class, 'log']);

    Route::prefix('fcm')->group(function () {
        Route::post('token', [FcmController::class, 'saveToken']);
        Route::delete('token', [FcmController::class, 'deleteToken']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::patch('v1/mood', [V1UserMoodController::class, 'updateMood']);
});
