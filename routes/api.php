<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('guest')->group(function () {
        Route::get('/user', [UserController::class, 'index']);
        Route::post('/user-login', [AuthController::class, 'login']);
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('patient')->group(function () {
            Route::get('/user/{id}', [UserController::class, 'show']);
        });
    });
    
});
