<?php

use App\Http\Controllers\Admin\MealFormController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\UserFormController;
use App\Http\Controllers\Admin\UserStatusController;
use App\Http\Controllers\Api\StripePaymentCallbackController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MealController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'message' => 'Welcome to Grocery API',
        'version' => '1.0.0',
        'documentation' => '/api/documentation',
    ]);
});

Route::prefix('payment')->group(function () {
    Route::get('/success', [StripePaymentCallbackController::class, 'success'])->name('payment.success');
    Route::get('/cancel', [StripePaymentCallbackController::class, 'cancel'])->name('payment.cancel');
});

// Admin Dashboard Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('categories', CategoryController::class);
        Route::get('meals/create', [MealFormController::class, 'create'])->name('meals.create');
        Route::get('meals/{meal}/edit', [MealFormController::class, 'edit'])->name('meals.edit');

        Route::resource('meals', MealController::class);

        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [OrderManagementController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/{order}/notes', [OrderManagementController::class, 'addNote'])->name('addNote');
            Route::delete('/{order}/notes/{note}', [OrderManagementController::class, 'deleteNote'])->name('deleteNote');
        });

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/create', [UserFormController::class, 'create'])->name('create');
            Route::get('/{user}/edit', [UserFormController::class, 'edit'])->name('edit');
            Route::patch('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle-active', [UserStatusController::class, 'toggleActive'])->name('toggleActive');
            Route::patch('/{user}/toggle-admin', [UserStatusController::class, 'toggleAdmin'])->name('toggleAdmin');
        });
    });
});
