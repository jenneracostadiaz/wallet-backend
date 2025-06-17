<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);

    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to dashboard!',
            'data' => 'This is protected content',
        ]);
    });

    Route::resource('currencies', CurrencyController::class)
        ->only(['index', 'show']);

    Route::resource('accounts', AccountController::class)->except(['create', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit']);
    Route::resource('transactions', TransactionController::class)
        ->except(['create', 'edit']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/balance', [DashboardController::class, 'balance']);
    });
});
