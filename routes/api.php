<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to dashboard!',
            'data' => 'This is protected content',
        ]);
    });

    Route::resource('currencies', CurrencyController::class)
        ->only(['index', 'show']);
});

