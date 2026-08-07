<?php

use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\FaqController;
use App\Http\Controllers\Dashboard\FavoriteController;
use App\Http\Controllers\Dashboard\MealController;
use App\Http\Controllers\Dashboard\MonitoringController;
use App\Http\Controllers\Dashboard\NotificationBroadcastController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\SmartListController;
use App\Http\Controllers\Dashboard\SubcategoryAjaxController;
use App\Http\Controllers\Dashboard\SubcategoryController;
use App\Http\Controllers\Dashboard\SupportController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\StripePaymentCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
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
Route::prefix('admin-dashboard')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Routes
    Route::middleware(['web', 'admin.web'])->group(function () {
        // Main Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Categories Management
        Route::resource('categories', CategoryController::class, ['as' => 'admin']);

        // Subcategories Management
        Route::resource('subcategories', SubcategoryController::class, ['as' => 'admin']);
        Route::get('subcategories-by-category/{categoryId}', [SubcategoryAjaxController::class, 'byCategory'])->name('admin.subcategories.by-category');

        // Meals / Products Management
        Route::resource('meals', MealController::class, ['as' => 'admin']);
        Route::post('meals/{id}/restore', [MealController::class, 'restore'])->name('admin.meals.restore');

        // Orders Management
        Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');

        // Users Management
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::patch('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('admin.users.toggle-active');

        // Smart Lists & Favorites Management
        Route::get('smart-lists', [SmartListController::class, 'index'])->name('admin.smart-lists.index');
        Route::get('favorites', [FavoriteController::class, 'index'])->name('admin.favorites.index');

        // FAQs Management
        Route::resource('faqs', FaqController::class, ['as' => 'admin']);
        Route::post('faqs/{id}/restore', [FaqController::class, 'restore'])->name('admin.faqs.restore');

        // Support Requests
        Route::get('support', [SupportController::class, 'index'])->name('admin.support.index');
        Route::get('support/{support}', [SupportController::class, 'show'])->name('admin.support.show');
        Route::patch('support/{support}/status', [SupportController::class, 'updateStatus'])->name('admin.support.update-status');
        Route::delete('support/{support}', [SupportController::class, 'destroy'])->name('admin.support.destroy');

        // Broadcasting Alerts / Notifications
        Route::get('notifications', [NotificationBroadcastController::class, 'index'])->name('admin.notifications.index');
        Route::get('notifications/create', [NotificationBroadcastController::class, 'create'])->name('admin.notifications.create');
        Route::post('notifications', [NotificationBroadcastController::class, 'store'])->name('admin.notifications.store');
        Route::delete('notifications/{id}', [NotificationBroadcastController::class, 'destroy'])->name('admin.notifications.destroy');

        // Analytics
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');

        // System Risks & Monitoring
        Route::get('monitoring', [MonitoringController::class, 'index'])->name('admin.monitoring');

        // General Settings
        Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });
});
