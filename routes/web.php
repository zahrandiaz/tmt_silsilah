<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SilsilahController;
use App\Http\Controllers\PdfController; 
use App\Http\Controllers\PageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\GedcomImportController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ReportController; // <-- TAMBAHKAN INI
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckSilsilahPrivacy;
use Illuminate\Support\Facades\Route;

// Rute untuk Halaman Statis (Publik)
Route::get('/tentang', [PageController::class, 'about'])->name('pages.about');
Route::get('/donasi', [PageController::class, 'donation'])->name('pages.donation');

Route::get('/', [SilsilahController::class, 'index'])->name('silsilah.index');

// Rute Dashboard sekarang menunjuk ke Controller
Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// --- SEMUA RUTE 'PEOPLE' DIKELOMPOKKAN DI SINI ---
Route::middleware('auth')->group(function () {
    Route::resource('people', PersonController::class)->except(['show']);
});

// Rute 'show' yang memiliki parameter, didefinisikan SETELAHNYA.
Route::get('/people/{person}', [PersonController::class, 'show'])
    ->middleware(CheckSilsilahPrivacy::class)
    ->name('people.show');
// --- AKHIR PENGELOMPOKAN RUTE PEOPLE ---


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Foto
    Route::post('/people/{person}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('/photos/{photo}/set-as-profile', [PhotoController::class, 'setAsProfilePicture'])->name('photos.set_as_profile');
    
    // Rute PDF
    Route::get('/export-pdf', [PdfController::class, 'showExportForm'])->name('pdf.export.form');
    Route::post('/export-pdf', [PdfController::class, 'generatePdf'])->name('pdf.generate');

    // Grup Rute Khusus Admin
    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/export/gedcom', [ExportController::class, 'exportGedcom'])->name('export.gedcom');
        Route::resource('announcements', AnnouncementController::class)->except(['show']);
        Route::get('/import/gedcom', [GedcomImportController::class, 'showForm'])->name('import.gedcom.form');
        Route::post('/import/gedcom', [GedcomImportController::class, 'import'])->name('import.gedcom.store');

        // --- TAMBAHKAN RUTE LAPORAN DI SINI ---
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/people-without-birthdate', [ReportController::class, 'peopleWithoutBirthDate'])->name('people.no_birth_date');
            Route::get('/people-without-parents', [ReportController::class, 'peopleWithoutParents'])->name('people.no_parents');
            Route::get('/families-without-children', [ReportController::class, 'familiesWithoutChildren'])->name('families.no_children');
        });
        // ------------------------------------
    });
});

require __DIR__.'/auth.php';