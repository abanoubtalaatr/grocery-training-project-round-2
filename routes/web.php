<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MealController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\StripePaymentCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'name'          => config('app.name'),
        'message'       => 'Welcome to Grocery API',
        'version'       => '1.0.0',
        'documentation' => '/api/documentation',
        'admin_panel'   => url('/dashboard'),
    ]);
});

Route::prefix('payment')->group(function () {
    Route::get('/success', [StripePaymentCallbackController::class, 'success'])->name('payment.success');
    Route::get('/cancel',  [StripePaymentCallbackController::class, 'cancel'])->name('payment.cancel');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes — /dashboard
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')->name('admin.')->middleware('web')->group(function () {

    // ─── Auth (guest only) ───────────────────────────────────────────────
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');

    // ─── Protected (admin only) ──────────────────────────────────────────
    Route::middleware('admin.web')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

        // Meals / Products
        Route::get('meals', [MealController::class, 'index'])->name('meals.index');

        // Categories
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

        // Reviews
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    });
});

