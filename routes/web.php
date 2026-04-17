<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk mengarahkan ke Google
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');

// Route callback setelah login Google berhasil
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);