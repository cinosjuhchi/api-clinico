<?php

use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\Api\V1\Doctor\IndexController;
use App\Http\Controllers\Api\V1\Auth\ClinicAuthController;
use App\Http\Controllers\Api\V1\Auth\DoctorAuthController;

Route::prefix('v1')->group(function () {
    Route::prefix('guest')->group(function () {
        Route::get('/user', [UserController::class, 'index']);
        Route::get('email/verify/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('/user-login', [AuthController::class, 'login']);
        Route::post('/doctor-login', [DoctorAuthController::class, 'login']);
        Route::post('/clinic-login', [ClinicAuthController::class, 'login']);
        Route::post('/user-register', [AuthController::class, 'store']);
        Route::post('/contact-us', [ContactUsController::class, 'send']);
        Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:api')->name('verification.resend');
    });
    Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
        Route::prefix('patient')->group(function () {
            Route::get('/user/{id}', [UserController::class, 'show']);
            Route::get('/logout-user', [AuthController::class, 'logout']);

            // profile route
            Route::prefix('me')->group(function () {
                Route::get('/user', [ProfileController::class, 'me']);
                Route::post('/update/demographic', [ProfileController::class, 'setDemographic']);
                Route::post('/update/chronical', [ProfileController::class, 'setChronicHealth']);
            });
        });
    });

    Route::middleware(['auth:sanctum', 'abilities:doctor'])->group(function () {
        Route::prefix('doctor')->group(function () {
            Route::get('/doctor/{id}', [IndexController::class, 'show']);
        });
    });
    
});
