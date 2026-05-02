<?php

use Illuminate\Support\Facades\Route;

// File ini biarkan saja kosong atau isi dengan info dasar
Route::get('/', function () {
    return response()->json(['message' => 'Monefy API is Running']);
});