<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduledPaymentController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);

    Route::resource('currencies', CurrencyController::class)
        ->only(['index', 'show']);

    Route::get('accounts/{account}/export-pdf', [AccountController::class, 'exportPdf']);
    Route::get('accounts/{account}/export-csv', [AccountController::class, 'exportCsv']);
    Route::resource('accounts', AccountController::class)->except(['create', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit']);
    Route::get('transactions/export-csv', [TransactionController::class, 'exportCsv']);
    Route::resource('transactions', TransactionController::class)
        ->except(['create', 'edit']);

    // Scheduled payments - specific routes MUST come before resource routes
    Route::get('scheduled-payments/upcoming', [ScheduledPaymentController::class, 'upcomingPayments']);
    Route::get('scheduled-payments/notifications', [ScheduledPaymentController::class, 'notifications']);
    Route::post('scheduled-payments/{scheduled_payment}/execute', [ScheduledPaymentController::class, 'executePayment']);
    Route::resource('scheduled-payments', ScheduledPaymentController::class)
        ->except(['create', 'edit']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/balance', [DashboardController::class, 'balance']);
        Route::get('/monthly-report', [DashboardController::class, 'monthlyReport']);
        Route::get('/monthly-comparison', [DashboardController::class, 'monthlyComparison']);
        Route::get('/quick-stats', [DashboardController::class, 'quickStats']);
    });
});
