<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminContactController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminNotificationController;
use App\Http\Controllers\Api\Admin\AdminOfferController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminReviewController;
use App\Http\Controllers\Api\Admin\AdminSettingController;
use App\Http\Controllers\Api\Admin\AdminSubcategoryController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CreateOrderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SetDefaultAddressController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\SpecialNoteController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\StripeCheckoutController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\CategoryController as ControllersCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
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
        Route::get('/{address}', [AddressController::class, 'show']);
        Route::put('/{address}', [AddressController::class, 'update']);
        Route::delete('/{address}', [AddressController::class, 'destroy']);
        Route::post('/{address}/set-default', SetDefaultAddressController::class);
    });

    Route::post('smart-lists/{id}/meals', [SmartListController::class, 'addMeal']);
    Route::delete('smart-lists/{id}/meals/{mealId}', [SmartListController::class, 'removeMeal']);
    Route::apiResource('smart-lists', SmartListController::class);

    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index']);
        Route::put('/', [NotificationSettingsController::class, 'update']);
        Route::put('/category/{category}', [NotificationSettingsController::class, 'updateCategory']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/with-resources', [NotificationController::class, 'indexWithResources']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/recent', [NotificationController::class, 'recent']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/{id}/unread', [NotificationController::class, 'markAsUnread']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/delete-multiple', [NotificationController::class, 'destroyMultiple']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
        Route::get('/type/{type}', [NotificationController::class, 'byType']);
    });

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'addItem']);
        Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });

    // Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/{mealId}/toggle', [FavoriteController::class, 'toggle']);
        Route::get('/{mealId}/check', [FavoriteController::class, 'check']);
        Route::delete('/{mealId}', [FavoriteController::class, 'remove']);
    });

    // Chatbot routes
    Route::prefix('chatbot')->group(function () {
        Route::post('/', [ChatbotController::class, 'chat']);
        Route::get('/history', [ChatbotController::class, 'history']);
        Route::get('/suggestions', [ChatbotController::class, 'suggestions']);
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
        Route::get('/{id}', [OrderController::class, 'show']);
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

    // Personalized "frequency" meals
    Route::get('/frequency', [MealController::class, 'frequency']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('dashboard/revenue-chart', [AdminDashboardController::class, 'revenueChart']);
        Route::get('dashboard/recent-activity', [AdminDashboardController::class, 'recentActivity']);

        // Users Management
        Route::apiResource('users', AdminUserController::class);
        Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::get('users/{user}/orders', [AdminUserController::class, 'userOrders']);
        Route::get('users-stats', [AdminUserController::class, 'stats']);

        // Orders Management
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('orders-stats', [AdminOrderController::class, 'stats']);
        Route::get('orders-export', [AdminOrderController::class, 'export']);

        // Products Management
        Route::apiResource('products', AdminProductController::class);
        Route::patch('products/{product}/toggle-availability', [AdminProductController::class, 'toggleAvailability']);
        Route::patch('products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured']);
        Route::post('products/{product}/images', [AdminProductController::class, 'uploadImages']);
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage']);
        Route::get('products-stats', [AdminProductController::class, 'stats']);

        // Categories Management
        Route::apiResource('categories', AdminCategoryController::class);
        Route::patch('categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus']);

        // Subcategories Management
        Route::apiResource('subcategories', AdminSubcategoryController::class);
        Route::patch('subcategories/{subcategory}/toggle-status', [AdminSubcategoryController::class, 'toggleStatus']);

        // Offers Management
        Route::apiResource('offers', AdminOfferController::class);
        Route::patch('offers/{offer}/toggle-status', [AdminOfferController::class, 'toggleStatus']);

        // Reviews Management
        Route::get('reviews', [AdminReviewController::class, 'index']);
        Route::get('reviews/{review}', [AdminReviewController::class, 'show']);
        Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve']);
        Route::patch('reviews/{review}/reject', [AdminReviewController::class, 'reject']);
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy']);
        Route::get('reviews-stats', [AdminReviewController::class, 'stats']);

        // Contact Messages
        Route::get('contacts', [AdminContactController::class, 'index']);
        Route::get('contacts/{contact}', [AdminContactController::class, 'show']);
        Route::patch('contacts/{contact}/mark-as-read', [AdminContactController::class, 'markAsRead']);
        Route::patch('contacts/{contact}/mark-as-replied', [AdminContactController::class, 'markAsReplied']);
        Route::patch('contacts/{contact}/mark-as-spam', [AdminContactController::class, 'markAsSpam']);
        Route::delete('contacts/{contact}', [AdminContactController::class, 'destroy']);
        Route::get('contacts-stats', [AdminContactController::class, 'stats']);

        // Settings
        Route::get('settings', [AdminSettingController::class, 'index']);
        Route::put('settings', [AdminSettingController::class, 'update']);

        // Notifications
        Route::get('notifications', [AdminNotificationController::class, 'index']);
        Route::post('notifications/send', [AdminNotificationController::class, 'send']);
        Route::post('notifications/send-to-all', [AdminNotificationController::class, 'sendToAll']);
    });
});

// Public routes
Route::get('/meals/today', [MealController::class, 'today']);
Route::get('meals/hot', [MealController::class, 'hot']);
Route::get('/meals/recommendations', [MealController::class, 'recommendations']);
Route::get('/meals', [MealController::class, 'index']);
Route::get('/meals/{id}', [MealController::class, 'show']);
Route::get('/new-products', [MealController::class, 'newProducts']);
Route::get('best-sells', [MealController::class, 'bestSells']);
Route::get('sliders', [MealController::class, 'slider']);
Route::get('brands', [MealController::class, 'brands']);
Route::get('more-to-explore', [MealController::class, 'moreToExplore']);
Route::get('settings', [SettingController::class, 'index']);
Route::get('special-notes', [SpecialNoteController::class, 'index']);

// Offers routes
Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('/featured', [OfferController::class, 'featured']);
    Route::get('/validate', [OfferController::class, 'validateOffer']);
    Route::get('/{code}', [OfferController::class, 'showByCode']);
});

// Categories routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/meals', [CategoryController::class, 'meals']);
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
Route::post('/contact', [ContactController::class, 'submit']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});

Route::apiResource('categories-ver2', ControllersCategoryController::class);