<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\AiScanController;
use App\Http\Controllers\Api\AnalyticController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/ai/scan-receipt', [AiScanController::class, 'scan']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wallets', [WalletController::class, 'store']);
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::put('/wallets/{id}', [WalletController::class, 'update']);
    Route::delete('/wallets/{id}', [WalletController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wishlists', [WishlistController::class, 'index']);
    Route::post('/wishlists', [WishlistController::class, 'store']);
    Route::get('/wishlists/{wishlist}', [WishlistController::class, 'show']);
    Route::put('/wishlists/{wishlist}', [WishlistController::class, 'update']); 
    Route::delete('/wishlists/{wishlist}', [WishlistController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Grouping route analytics
    Route::prefix('analytic')->group(function () {
        Route::get('/summary', [AnalyticController::class, 'getSummary']);
        Route::get('/top-categories', [AnalyticController::class, 'getTopCategories']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    Route::get('/dashboard/summary', [DashboardController::class, 'getSummary']);
    Route::get('/dashboard/transactions', [DashboardController::class, 'getTransactions']);
    Route::post('/bills', [\App\Http\Controllers\Api\BillController::class, 'store']);
    Route::get('/bills', [\App\Http\Controllers\Api\BillController::class, 'index']);
    Route::put('/bills/{bill}', [\App\Http\Controllers\Api\BillController::class, 'update']);
    Route::delete('/bills/{bill}', [\App\Http\Controllers\Api\BillController::class, 'destroy']);
    Route::post('/profile/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'uploadAvatar']);
});

