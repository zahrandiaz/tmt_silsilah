<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SilsilahController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckSilsilahPrivacy;
use Illuminate\Support\Facades\Route;


//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/', [SilsilahController::class, 'index'])->name('silsilah.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- SEMUA RUTE 'PEOPLE' DIKELOMPOKKAN DI SINI ---
Route::middleware('auth')->group(function () {
    // Rute resource (create, store, edit, update, destroy, index) didefinisikan DULUAN.
    // Ini memastikan /people/create tidak tertimpa oleh /people/{person}.
    Route::resource('people', PersonController::class)->except(['show']);
});

// Rute 'show' yang memiliki parameter, didefinisikan SETELAHNYA.
// Rute ini juga diberi middleware privasi.
Route::get('/people/{person}', [PersonController::class, 'show'])
    ->middleware(CheckSilsilahPrivacy::class)
    ->name('people.show');
// --- AKHIR PENGELOMPOKAN RUTE PEOPLE ---


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- AWAL PENAMBAHAN RUTE FOTO ---
    Route::post('/people/{person}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    // --- AKHIR PENAMBAHAN RUTE FOTO ---
    
    // Route::resource('people', ...) sudah dipindahkan ke atas

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/export/gedcom', [ExportController::class, 'exportGedcom'])->name('export.gedcom');
    });
});

require __DIR__.'/auth.php';