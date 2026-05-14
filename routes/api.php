<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\AiScanController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/ai/scan-receipt', [AiScanController::class, 'scan']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wallets', [WalletController::class, 'store']);
    Route::get('/wallets', [WalletController::class, 'index']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wishlists', [WishlistController::class, 'index']);
    Route::post('/wishlists', [WishlistController::class, 'store']);
    Route::put('/wishlists/{id}', [WishlistController::class, 'update']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);
    Route::get('/dashboard/transactions', [DashboardController::class, 'getTransactions']);
    Route::post('/saving-goals', [\App\Http\Controllers\Api\SavingGoalController::class, 'store']);
    Route::get('/saving-goals', [\App\Http\Controllers\Api\SavingGoalController::class, 'index']);
    Route::post('/bills', [\App\Http\Controllers\Api\BillController::class, 'store']);
    Route::get('/bills', [\App\Http\Controllers\Api\BillController::class, 'index']);
    Route::put('/bills/{id}/pay', [\App\Http\Controllers\Api\BillController::class, 'markAsPaid']);
    Route::put('/bills/{id}/unpay', [\App\Http\Controllers\Api\BillController::class, 'markAsUnpaid']);
});

