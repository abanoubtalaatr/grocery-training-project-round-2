<?php

use App\Http\Controllers\StripePaymentCallbackController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
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



Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/', [DashboardController::class, 'index']);
    Route::post('/meals', [DashboardController::class, 'storeMeal']);
    Route::put('/meals/{meal}', [DashboardController::class, 'updateMeal']);
    Route::delete('/meals/{meal}', [DashboardController::class, 'destroyMeal']);
});