<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->get('me', [AuthController::class, 'me']);

Route::middleware('auth:api')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('parts', PartController::class);
});

