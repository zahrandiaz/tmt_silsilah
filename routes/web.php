<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SettingController;
// use App\Http\Controllers\TreeController; // Dihapus
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckSilsilahPrivacy;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Grup Rute yang Dilindungi oleh Middleware Privasi
Route::middleware(CheckSilsilahPrivacy::class)->group(function () {
    // Rute untuk melihat detail individu sekarang di sini
    Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource 'people' sekarang tidak lagi menyertakan 'show'
    Route::resource('people', PersonController::class)->except(['show']); 

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/export/gedcom', [ExportController::class, 'exportGedcom'])->name('export.gedcom');
    });
});

require __DIR__.'/auth.php';