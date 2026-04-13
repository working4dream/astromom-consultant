<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetUserDisplayTimezone;

use App\Http\Controllers\API\v2\CustomerController;
use App\Http\Controllers\API\v2\AppointmentController;
use App\Http\Controllers\API\v2\ChatController;

Route::prefix('customer')->group(function () {
    Route::middleware(['checkBearerToken', SetUserDisplayTimezone::class])->group(function () {
        // Appointment
        Route::get('get-astrologer-schedule/{id}', [AppointmentController::class, 'getAstrologerSchedule']);
        // Astrologer
        Route::get('get-astrologer-detail/{id}', [CustomerController::class, 'getAstrologerDetail']);
    });
});

// Chat
Route::prefix('chat')->group(function () {
    Route::middleware(['checkBearerToken', SetUserDisplayTimezone::class])->group(function () {
        Route::get('/conversations', [ChatController::class, 'conversationList']);
    });
});