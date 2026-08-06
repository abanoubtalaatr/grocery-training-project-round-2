<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\DeleteAccountController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyOtpController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Chatbot\GetChatHistoryController;
use App\Http\Controllers\Api\Chatbot\GetChatSuggestionsController;
use App\Http\Controllers\Api\Chatbot\SendChatMessageController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\Faq\GetFaqCategoriesController;
use App\Http\Controllers\Api\Faq\GetFaqsByCategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FavoriteStatusController;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\MealBestSellsController;
use App\Http\Controllers\Api\MealBrandsController;
use App\Http\Controllers\Api\MealFrequencyController;
use App\Http\Controllers\Api\MealHotController;
use App\Http\Controllers\Api\MealMoreToExploreController;
use App\Http\Controllers\Api\MealNewProductsController;
use App\Http\Controllers\Api\MealRecommendationsController;
use App\Http\Controllers\Api\MealSliderController;
use App\Http\Controllers\Api\MealTodayController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\UpdateNotificationCategoryController;
use App\Http\Controllers\Api\Notification\ClearAllNotificationsController;
use App\Http\Controllers\Api\Notification\DestroyMultipleNotificationsController;
use App\Http\Controllers\Api\Notification\GetNotificationsByTypeController;
use App\Http\Controllers\Api\Notification\GetNotificationsWithResourcesController;
use App\Http\Controllers\Api\Notification\GetNotificationStatsController;
use App\Http\Controllers\Api\Notification\GetRecentNotificationsController;
use App\Http\Controllers\Api\Notification\GetUnreadNotificationsCountController;
use App\Http\Controllers\Api\Notification\MarkAllNotificationsAsReadController;
use App\Http\Controllers\Api\Notification\MarkNotificationAsReadController;
use App\Http\Controllers\Api\Notification\MarkNotificationAsUnreadController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\Offer\GetFeaturedOffersController;
use App\Http\Controllers\Api\Offer\ShowOfferByCodeController;
use App\Http\Controllers\Api\Offer\ValidateOfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\Payment\GetPaymentHistoryController;
use App\Http\Controllers\Api\Payment\GetPaymentReceiptController;
use App\Http\Controllers\Api\Payment\CreateCheckoutSessionController;
use App\Http\Controllers\Api\Payment\VerifyCheckoutSessionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileImageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\Review\GetMealReviewsController;
use App\Http\Controllers\Api\Review\GetMealReviewStatsController;
use App\Http\Controllers\Api\Review\GetUserReviewsController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SetDefaultAddressController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\Setting\GetPublicSettingsController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\SpecialNoteController;
use App\Http\Controllers\Api\StaticPageController;
use App\Http\Controllers\Api\StaticPage\GetImportantPagesController;
use App\Http\Controllers\Api\StaticPage\ShowStaticPageBySlugController;
use App\Http\Controllers\Api\Stripe\ChargeSavedCardController;
use App\Http\Controllers\Api\Stripe\CreateSetupIntentController;
use App\Http\Controllers\Api\Stripe\DeleteCardController;
use App\Http\Controllers\Api\Stripe\ListCardsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubcategoryController;
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

Route::post('/stripe/webhook', StripeWebhookController::class);

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/verify-otp', VerifyOtpController::class);
    Route::post('/reset-password', ResetPasswordController::class);
    Route::post('/google', GoogleAuthController::class);
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', LogoutController::class);
        Route::post('/change-password', ChangePasswordController::class);
        Route::delete('/delete-account', DeleteAccountController::class);
        Route::get('/me', MeController::class);
    });

    // Profile routes
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/update', [ProfileController::class, 'update']);
        Route::post('/image', [ProfileImageController::class, 'update']);
        Route::delete('/image', [ProfileImageController::class, 'destroy']);
        
        // Sessions management
        Route::get('/sessions', [SessionController::class, 'index']);
        Route::delete('/sessions/{tokenId}', [SessionController::class, 'destroy']);
    });

    Route::apiResource('addresses', AddressController::class);
    Route::match(['put', 'patch'], '/set-default-address/{address}', SetDefaultAddressController::class);
    Route::apiResource('smart-lists', SmartListController::class);

    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'index']);
        Route::put('/', [NotificationSettingsController::class, 'update']);
        Route::put('/category/{category}', UpdateNotificationCategoryController::class);
    });

    Route::prefix('notifications')->group(function () {
        // Get notifications
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/with-resources', GetNotificationsWithResourcesController::class);

        // Statistics
        Route::get('/stats', GetNotificationStatsController::class);
        Route::get('/unread-count', GetUnreadNotificationsCountController::class);
        Route::get('/recent', GetRecentNotificationsController::class);

        // Single notification operations
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', MarkNotificationAsReadController::class);
        Route::put('/{id}/unread', MarkNotificationAsUnreadController::class);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);

        // Bulk operations
        Route::put('/mark-all-read', MarkAllNotificationsAsReadController::class);
        Route::delete('/delete-multiple', DestroyMultipleNotificationsController::class);
        Route::delete('/clear-all', ClearAllNotificationsController::class);

        // Filtered notifications
        Route::get('/type/{type}', GetNotificationsByTypeController::class);
    });

    Route::apiResource('cart', CartController::class)->only('index', 'destroy');
    Route::apiResource('cart-items', CartItemsController::class)->only('store', 'update', 'destroy');

    // Favorites routes
    Route::apiResource('favorites', FavoriteController::class)->only('index', 'store', 'destroy');
    Route::get('favorites/{meal}/status', FavoriteStatusController::class);

    // Chatbot routes
    Route::prefix('chatbot')->group(function () {
        Route::post('/', SendChatMessageController::class);
        Route::get('/history', GetChatHistoryController::class);
        Route::get('/suggestions', GetChatSuggestionsController::class);
    });

    Route::get('/cards', ListCardsController::class);
    Route::post('/setup-intent', CreateSetupIntentController::class);
    Route::post('/charge-card', ChargeSavedCardController::class);
    Route::delete('/cards/{id}', DeleteCardController::class);

    // Order routes
    Route::apiResource('orders', OrderController::class)->only('index', 'store', 'show');
    Route::get('orders/track', OrderTrackingController::class);

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::post('/stripe/checkout-session', CreateCheckoutSessionController::class);
        Route::get('/stripe/verify-session/{session_id}', VerifyCheckoutSessionController::class);
        Route::get('/history', GetPaymentHistoryController::class);
        Route::get('/receipt/{order}', GetPaymentReceiptController::class);
        Route::get('/invoice/{order}', GetPaymentReceiptController::class);
    });

    // Dashboard route
    Route::get('/dashboard', DashboardController::class);

    // Personalized "frequency" meals (requires auth — uses order history)
    Route::get('/frequency', MealFrequencyController::class);

    // User reviews (requires auth)
    Route::get('/user/reviews', GetUserReviewsController::class);
    Route::apiResource('reviews', ReviewController::class)->only('store', 'update', 'destroy');
});

// Meals routes
Route::prefix('meals')->group(function () {
    Route::get('/today', MealTodayController::class);
    Route::get('hot', MealHotController::class);
    Route::get('/recommendations', MealRecommendationsController::class);
    Route::get('/', [MealController::class, 'index']);
    Route::get('/{id}', [MealController::class, 'show']);
    Route::get('/{meal}/reviews', GetMealReviewsController::class);
    Route::get('/{meal}/reviews/stats', GetMealReviewStatsController::class);
});

Route::get('/new-products', MealNewProductsController::class);
Route::get('best-sells', MealBestSellsController::class);
Route::get('sliders', MealSliderController::class);
Route::get('brands', MealBrandsController::class);
Route::get('more-to-explore', MealMoreToExploreController::class);
Route::get('settings', [SettingController::class, 'index']);
Route::get('settings/public', GetPublicSettingsController::class);
Route::get('special-notes', [SpecialNoteController::class, 'index']);

Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('/featured', GetFeaturedOffersController::class);
    Route::get('/validate', ValidateOfferController::class);
    Route::get('/{code}', ShowOfferByCodeController::class);
});

Route::apiResource('categories', CategoryController::class)->only('index', 'show');
// Subcategories routes
Route::apiResource('subcategories', CategoryController::class)->only('index', 'show');

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/faqs/categories', GetFaqCategoriesController::class);
Route::get('/faqs/category/{category}', GetFaqsByCategoryController::class);

Route::get('/pages', [StaticPageController::class, 'index']);
Route::get('/pages/slug/{slug}', ShowStaticPageBySlugController::class);
Route::get('/pages/important', GetImportantPagesController::class);

Route::post('/contact', [ContactController::class, 'submit']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
    ]);
});
