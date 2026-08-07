<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StripePaymentCallbackController;
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

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/categories', [DashboardController::class, 'storeCategory'])->name('dashboard.categories.store');
    Route::put('/categories/{category}', [DashboardController::class, 'updateCategory'])->name('dashboard.categories.update');
    Route::delete('/categories/{category}', [DashboardController::class, 'destroyCategory'])->name('dashboard.categories.destroy');
});
