<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', [UserController::class, 'consume']);
Route::post('/login', [UserController::class, 'login']);
