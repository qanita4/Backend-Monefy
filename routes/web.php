<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk mengarahkan ke Google
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');

// Route callback setelah login Google berhasil
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wallets', [WalletController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Route untuk mencatat transaksi baru
    Route::post('/transactions', [TransactionController::class, 'store']);
    
    // Kamu juga bisa tambah route untuk melihat history nanti
    Route::get('/transactions', [TransactionController::class, 'index']);
});