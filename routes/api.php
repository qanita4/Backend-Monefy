<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wallets', [WalletController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);
    Route::get('/dashboard/transactions', [DashboardController::class, 'getTransactions']);
});

