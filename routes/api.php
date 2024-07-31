<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ContactUsController;

Route::prefix('v1')->group(function () {
    Route::prefix('guest')->group(function () {
        Route::get('/user', [UserController::class, 'index']);
        Route::post('/user-login', [AuthController::class, 'login']);
        Route::post('/user-register', [AuthController::class, 'store']);
        Route::post('/contact-us', [ContactUsController::class, 'send']);
        Route::get('email/verify/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:api')->name('verification.resend');
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('patient')->group(function () {
            Route::get('/user/{id}', [UserController::class, 'show']);
        });
    });
    
});
