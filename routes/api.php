<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute ini dilindungi dan hanya bisa diakses oleh pengguna yang sudah login
Route::middleware('auth:sanctum')->group(function () {
    // Rute default untuk mendapatkan data user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute pencarian telah dipindahkan ke routes/web.php
});