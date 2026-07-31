<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\CourseController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PresentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PresentationController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Interactive presentation (examples for Session / Sanctum / Passport / JWT)
|--------------------------------------------------------------------------
*/
Route::prefix('learn')->name('presentation.')->group(function () {
    Route::get('/', [PresentationController::class, 'index'])->name('index');
    Route::get('/session', [PresentationController::class, 'session'])->name('session');
    Route::get('/sanctum', [PresentationController::class, 'sanctum'])->name('sanctum');
    Route::get('/passport', [PresentationController::class, 'passport'])->name('passport');
    Route::get('/jwt', [PresentationController::class, 'jwt'])->name('jwt');
    Route::get('/compare', [PresentationController::class, 'compare'])->name('compare');
    Route::get('/demos', [PresentationController::class, 'demos'])->name('demos');
});

/*
|--------------------------------------------------------------------------
| Phase 1 — Session Authentication (Blade LMS)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('courses', CourseController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
