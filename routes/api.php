<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryMealController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DriverOrderController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\MealViewController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\SmartListMealController;
use App\Http\Controllers\Api\SpecialNoteController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\StripeCheckoutController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\UserActivityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('/verify-mfa', [AuthController::class, 'verifyMfaAndLogin'])->middleware('throttle:otp');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:otp');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:otp');
    Route::post('/google', [GoogleAuthController::class, 'login'])->middleware('throttle:auth');
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:otp');
        Route::post('/change-password/confirm', [AuthController::class, 'confirmChangePassword'])->middleware('throttle:otp');
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

    Route::apiResource('smart-lists', SmartListController::class)
        ->names('api.smart-lists');
    Route::post('smart-lists/{smart_list}/add-to-cart', [SmartListController::class, 'addToCart']);

    Route::prefix('smart-lists/{smart_list}/meals')->group(function () {
        Route::post('/', [SmartListMealController::class, 'store']);
        Route::delete('/{meal}', [SmartListMealController::class, 'destroy']);
    });

    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index']);
        Route::put('/', [NotificationSettingsController::class, 'update']);
        Route::put('/category/{category}', [NotificationSettingsController::class, 'updateCategory']);
    });

    Route::prefix('notifications')->group(function () {
        // Get notifications
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/with-resources', [NotificationController::class, 'indexWithResources']);

        // Statistics
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/recent', [NotificationController::class, 'recent']);

        // Single notification operations
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/{id}/unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);

        // Bulk operations
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-multiple', [NotificationController::class, 'destroyMultiple']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);

        // Filtered notifications
        Route::get('/type/{type}', [NotificationController::class, 'byType']);
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::post('/coupon', [CartController::class, 'applyCoupon']);
        Route::delete('/coupon', [CartController::class, 'removeCoupon']);
    });

    // Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/{mealId}/toggle', [FavoriteController::class, 'toggle']);
        Route::get('/{mealId}/check', [FavoriteController::class, 'check']);
        Route::delete('/{mealId}', [FavoriteController::class, 'remove']);
    });

    // Chatbot routes
    Route::prefix('chatbot')->middleware('throttle:chatbot')->group(function () {
        Route::post('/', [ChatbotController::class, 'chat']);
        Route::get('/history', [ChatbotController::class, 'history']);
        Route::get('/suggestions', [ChatbotController::class, 'suggestions']);
        Route::post('/handoff', [ChatbotController::class, 'handoff']);
    });

    Route::get('/cards', [StripeController::class, 'listCards']);
    Route::post('/setup-intent', [StripeController::class, 'createSetupIntent']);
    Route::post('/charge-card', [StripeController::class, 'chargeSavedCard']);
    Route::delete('/cards/{id}', [StripeController::class, 'deleteCard']);

    // Order routes
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/track', [OrderController::class, 'track']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);

        // Order chat (customer <-> driver)
        Route::get('/{order}/messages', [OrderChatController::class, 'index']);
        Route::post('/{order}/messages', [OrderChatController::class, 'store'])->middleware('throttle:chatbot');
        Route::get('/{order}/messages/unread', [OrderChatController::class, 'unreadCount']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::post('/stripe/checkout-session', [StripeCheckoutController::class, 'store']);
        Route::get('/stripe/verify-session/{session_id}', [StripeCheckoutController::class, 'verifySession']);
        Route::get('/history', [PaymentController::class, 'paymentHistory']);
        Route::get('/receipt/{order}', [PaymentController::class, 'receipt']);
        Route::get('/invoice/{order}', [PaymentController::class, 'invoice']);
    });

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Analytics tracking routes
    Route::post('/meals/{meal}/view', [MealViewController::class, 'track']);
    Route::post('/activity', [UserActivityController::class, 'track']);

    // Personalized "frequency" meals (requires auth — uses order history)
    Route::get('/frequency', [MealController::class, 'frequency']);

    // Push token management
    Route::prefix('push-tokens')->group(function () {
        Route::get('/', [PushTokenController::class, 'index']);
        Route::post('/', [PushTokenController::class, 'store'])->middleware('throttle:auth');
        Route::delete('/{token}', [PushTokenController::class, 'destroy']);
    });

    // Support routes
    Route::get('/support/my-messages', [ContactController::class, 'myMessages']);
    Route::middleware('role:support_agent,admin')->prefix('support')->group(function () {
        Route::get('/messages', [ContactController::class, 'index']);
        Route::get('/messages/statistics', [ContactController::class, 'statistics']);
        Route::get('/messages/{contactMessage}', [ContactController::class, 'show']);
        Route::put('/messages/{contactMessage}/status', [ContactController::class, 'updateStatus']);
        Route::post('/messages/{contactMessage}/reply', [ContactController::class, 'reply']);
        Route::delete('/messages/{contactMessage}', [ContactController::class, 'destroy']);
    });

    // Driver / Delivery Partner routes
    Route::middleware('role:delivery_partner,admin')->prefix('driver')->group(function () {
        Route::get('/orders', [DriverOrderController::class, 'index']);
        Route::get('/orders/{order}', [DriverOrderController::class, 'show']);
        Route::post('/orders/{order}/status', [DriverOrderController::class, 'updateStatus']);
        Route::post('/location', [DriverOrderController::class, 'updateLocation']);

        // Driver chat
        Route::get('/orders/{order}/messages', [DriverOrderController::class, 'messages']);
        Route::post('/orders/{order}/messages', [DriverOrderController::class, 'sendMessage'])->middleware('throttle:chatbot');
        Route::get('/orders/{order}/messages/unread', [DriverOrderController::class, 'unreadCount']);
    });
});

// Meals routes
Route::prefix('meals')->group(function () {
    Route::get('/today', [MealController::class, 'today']);
    Route::get('hot', [MealController::class, 'hot']);

    Route::get('/recommendations', [MealController::class, 'recommendations']);
    Route::get('/', [MealController::class, 'index']);
    Route::get('/{id}', [MealController::class, 'show']);
});
Route::get('/new-products', [MealController::class, 'newProducts']);
Route::get('best-sells', [MealController::class, 'bestSells']);
Route::get('sliders', [MealController::class, 'slider']);
Route::get('brands', [MealController::class, 'brands']);
Route::get('more-to-explore', [MealController::class, 'moreToExplore']);
Route::get('settings', [SettingController::class, 'index']);
Route::get('special-notes', [SpecialNoteController::class, 'index']);
// Categories routes

Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('/featured', [OfferController::class, 'featured']);
    Route::get('/validate', [OfferController::class, 'validateOffer']);
    Route::get('/{code}', [OfferController::class, 'showByCode']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}/meals', [CategoryMealController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);
});

// Subcategories routes
Route::prefix('subcategories')->group(function () {
    Route::get('/', [SubcategoryController::class, 'index']);
    Route::get('/{id}', [SubcategoryController::class, 'show']);
    Route::get('/{id}/meals', [SubcategoryController::class, 'meals']);
});
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/pages', [StaticPageController::class, 'index']);
Route::get('/pages/slug/{slug}', [StaticPageController::class, 'showBySlug']);
Route::get('/pages/important', [StaticPageController::class, 'importantPages']);
Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:contact');

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});
