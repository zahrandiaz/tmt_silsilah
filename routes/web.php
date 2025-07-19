<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonController; // <-- 1. Tambahkan ini
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckSilsilahPrivacy;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 2. Grup Rute yang Dilindungi oleh Middleware Privasi
Route::middleware(CheckSilsilahPrivacy::class)->group(function () {
    Route::get('/tree', [TreeController::class, 'index'])->name('tree.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2. Tambahkan baris ini untuk rute silsilah
    Route::resource('people', PersonController::class); 

    // Terapkan middleware admin ke grup rute ini
    Route::middleware(AdminMiddleware::class)->group(function () {
        // Rute untuk Setting
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        // Nanti halaman manajemen user juga diletakkan di sini

        // Rute untuk Manajemen Pengguna
        Route::resource('users', UserController::class)->except(['show']);

        // Rute untuk Ekspor Data
        Route::get('/export/gedcom', [ExportController::class, 'exportGedcom'])->name('export.gedcom');
    });
});

require __DIR__.'/auth.php';