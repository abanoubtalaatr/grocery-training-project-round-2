<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\SpecialNoteController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\StripeCheckoutController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes map to the refactored thin controllers and actions.
|
*/

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/google', [GoogleAuthController::class, 'login']);
});

// Public content
Route::get('/meals/today', [MealController::class, 'today']);
Route::get('/meals/hot', [MealController::class, 'hot']);
Route::get('/meals/recommendations', [MealController::class, 'recommendations']);
Route::get('/meals', [MealController::class, 'index']);
Route::get('/meals/{id}', [MealController::class, 'show']);
Route::get('/sliders', [MealController::class, 'slider']);
Route::get('/new-products', [MealController::class, 'newProducts']);
Route::get('/best-sells', [MealController::class, 'bestSells']);
Route::get('/brands', [MealController::class, 'brands']);
Route::get('/more-to-explore', [MealController::class, 'moreToExplore']);

Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('/featured', [OfferController::class, 'featured']);
    Route::get('/validate', [OfferController::class, 'validateOffer']);
    Route::get('/{code}', [OfferController::class, 'showByCode']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/meals', [CategoryController::class, 'meals']);
});

Route::prefix('subcategories')->group(function () {
    Route::get('/', [SubcategoryController::class, 'index']);
    Route::get('/{id}', [SubcategoryController::class, 'show']);
    Route::get('/{id}/meals', [SubcategoryController::class, 'meals']);
});

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/pages', [StaticPageController::class, 'index']);
Route::get('/pages/slug/{slug}', [StaticPageController::class, 'showBySlug']);
Route::get('/pages/important', [StaticPageController::class, 'importantPages']);
Route::post('/contact', [ContactController::class, 'submit']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::post('/image', [ProfileController::class, 'updateImage']);
        Route::put('/info', [ProfileController::class, 'updateInfo']);
        Route::delete('/image', [ProfileController::class, 'deleteImage']);
        Route::get('/sessions', [ProfileController::class, 'sessions']);
        Route::delete('/sessions/{tokenId}', [ProfileController::class, 'destroySession']);
    });

    // Address routes
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::get('/{id}', [AddressController::class, 'show']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
    });

    // Smart lists
    Route::post('smart-lists/{id}/meals', [SmartListController::class, 'addMeal']);
    Route::delete('smart-lists/{id}/meals/{mealId}', [SmartListController::class, 'removeMeal']);
    Route::apiResource('smart-lists', SmartListController::class);

    // Notifications (simplified to match refactor)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::put('/notifications/settings', [NotificationController::class, 'updateSettings']);

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });

    // Favorites (refactored)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{mealId}', [FavoriteController::class, 'destroy']);

    // Chatbot
    Route::post('/chatbot', [ChatbotController::class, 'chat']);

    // Stripe saved cards
    Route::get('/cards', [StripeController::class, 'listCards']);
    Route::post('/setup-intent', [StripeController::class, 'createSetupIntent']);
    Route::post('/charge-card', [StripeController::class, 'chargeSavedCard']);
    Route::delete('/cards/{id}', [StripeController::class, 'deleteCard']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/track', [OrderController::class, 'track']);
        Route::get('/{id}', [OrderController::class, 'show']);

        Route::post('/{order}/send-invoice', [InvoiceController::class, 'sendInvoice']);
        Route::get('/{order}/download-invoice', [InvoiceController::class, 'downloadInvoice']);
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::post('/stripe/checkout-session', [StripeCheckoutController::class, 'store']);
        Route::get('/stripe/verify-session/{session_id}', [StripeCheckoutController::class, 'verifySession']);
        Route::get('/history', [PaymentController::class, 'paymentHistory']);
        Route::get('/receipt/{order}', [PaymentController::class, 'receipt']);
        Route::get('/invoice/{order}', [PaymentController::class, 'invoice']);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Frequency meals (requires auth)
    Route::get('/frequency', [MealController::class, 'frequency']);

    // Reviews
    Route::get('/meals/{mealId}/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});