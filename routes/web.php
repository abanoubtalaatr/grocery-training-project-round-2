<?php

use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\OrderChatController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\StripePaymentCallbackController;
use App\Http\Controllers\Web\AiAssistantController;
use App\Http\Controllers\Web\AddressController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FavoriteController;
use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SessionController;
use App\Http\Controllers\Web\SmartListController;
use App\Http\Controllers\Web\SmartListMealController;
use App\Http\Controllers\Web\SupportController;
use App\Models\Category;
use App\Models\Meal;
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
    $featuredCategories = Category::active()->ordered()->withCount(['meals' => fn($q) => $q->available()])->take(8)->get();
    $todayDeals = Meal::with('category')->available()->withActiveDiscount()->orderBy('created_at', 'desc')->take(8)->get();
    $recommendations = Meal::with('category')->available()->featured()->inRandomOrder()->take(6)->get();

    return view('home', compact('featuredCategories', 'todayDeals', 'recommendations'));
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'toggle'])->name('locale.toggle');

Route::prefix('payment')->group(function () {
    Route::get('/success', [StripePaymentCallbackController::class, 'success'])->name('payment.success');
    Route::get('/cancel', [StripePaymentCallbackController::class, 'cancel'])->name('payment.cancel');
});

Route::get('/search', [CatalogController::class, 'search'])->name('catalog.search');

Route::get('/categories', [CatalogController::class, 'categories'])->name('catalog.categories');
Route::get('/categories/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/subcategories/{subcategory:slug}', [CatalogController::class, 'subcategory'])->name('catalog.subcategory');
Route::get('/products', [CatalogController::class, 'meals'])->name('catalog.meals');
Route::get('/products/{meal:slug}', [CatalogController::class, 'meal'])->name('catalog.meal');
Route::get('/recommendations', [CatalogController::class, 'recommendations'])->name('catalog.recommendations');
Route::get('/today-deals', [CatalogController::class, 'todayDeals'])->name('catalog.today-deals');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot.form');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot')->middleware('throttle:otp');

    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset')->middleware('throttle:otp');

    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

// Help & Support ظ¤ public routes (accessible to guests)
Route::get('/help', [SupportController::class, 'index'])->name('support.index');
Route::get('/help/faqs', [SupportController::class, 'faqs'])->name('support.faqs');
Route::get('/help/policies', [SupportController::class, 'policies'])->name('support.policies');
Route::get('/help/contact', [SupportController::class, 'contact'])->name('support.contact');
Route::post('/help/contact', [SupportController::class, 'submitContact'])->name('support.contact.submit');

Route::get('/mfa/verify', [AuthController::class, 'showMfaForm'])->name('mfa.show');
Route::post('/mfa/verify', [AuthController::class, 'verifyMfa'])->name('mfa.verify')->middleware('throttle:otp');
Route::post('/mfa/resend', [AuthController::class, 'resendMfa'])->name('mfa.resend')->middleware('throttle:otp');

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI Assistant
    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant');

    // Chatbot API (web proxy ظ¤ reuses existing Api\ChatbotController with web session auth)
    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat')->middleware('throttle:chatbot');
    Route::get('/chatbot/history', [ChatbotController::class, 'history'])->name('chatbot.history');
    Route::get('/chatbot/suggestions', [ChatbotController::class, 'suggestions'])->name('chatbot.suggestions');
    Route::post('/chatbot/handoff', [ChatbotController::class, 'handoff'])->name('chatbot.handoff')->middleware('throttle:chatbot');

    // Help & Support ظ¤ auth-only routes
    Route::get('/help/messages', [SupportController::class, 'myMessages'])->name('support.messages');
    Route::get('/help/messages/{contactMessage}', [SupportController::class, 'showMessage'])->name('support.messages.show');

    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verify-email.form');
    Route::post('/verify-email', [AuthController::class, 'verifyEmailOtp'])->name('verify-email');
    Route::post('/verify-email/resend', [AuthController::class, 'resendEmailVerification'])->name('verify-email.resend');

    Route::get('/profile/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/profile/sessions/{id}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('/profile/sessions', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');

    Route::get('/profile/delete-account', [AuthController::class, 'confirmDeleteAccount'])->name('delete-account.confirm');
    Route::delete('/profile/delete-account', [AuthController::class, 'deleteAccount'])->name('delete-account');

    // Cart
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [CartController::class, 'addItem'])->name('cart.addItem');
    Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');

    // Payment / Invoice
    Route::get('/payment/invoice/{order}', [PaymentController::class, 'invoice'])->name('payment.invoice');
    Route::get('/payment/receipt/{order}', [PaymentController::class, 'receipt'])->name('payment.receipt');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('orders.track');
    Route::get('/orders/{order}/track/json', [OrderController::class, 'trackJson'])->name('orders.track-json');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Order chat (customer <-> driver)
    Route::get('/orders/{order}/messages', [OrderChatController::class, 'index'])->name('orders.messages.index');
    Route::post('/orders/{order}/messages', [OrderChatController::class, 'store'])->name('orders.messages.store');
    Route::get('/orders/{order}/messages/unread', [OrderChatController::class, 'unreadCount'])->name('orders.messages.unread');

    // Push token management
    Route::post('/push-tokens', [PushTokenController::class, 'store'])->name('push-tokens.store');
    Route::delete('/push-tokens/{token}', [PushTokenController::class, 'destroy'])->name('push-tokens.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::get('/profile/image', [ProfileController::class, 'image'])->name('profile.image');
    Route::post('/profile/image', [ProfileController::class, 'uploadImage'])->name('profile.image.upload');
    Route::delete('/profile/image', [ProfileController::class, 'deleteImage'])->name('profile.image.delete');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.setDefault');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{meal}/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{meal}', [FavoriteController::class, 'remove'])->name('favorites.remove');

    // Smart Lists
    Route::get('/smart-lists', [SmartListController::class, 'index'])->name('smart-lists.index');
    Route::get('/smart-lists/create', [SmartListController::class, 'create'])->name('smart-lists.create');
    Route::post('/smart-lists', [SmartListController::class, 'store'])->name('smart-lists.store');
    Route::get('/smart-lists/{smartList}', [SmartListController::class, 'show'])->name('smart-lists.show');
    Route::get('/smart-lists/{smartList}/edit', [SmartListController::class, 'edit'])->name('smart-lists.edit');
    Route::put('/smart-lists/{smartList}', [SmartListController::class, 'update'])->name('smart-lists.update');
    Route::delete('/smart-lists/{smartList}', [SmartListController::class, 'destroy'])->name('smart-lists.destroy');
    Route::post('/smart-lists/{smartList}/add-to-cart', [SmartListController::class, 'addToCart'])->name('smart-lists.addToCart');

    // Smart List Meals
    Route::post('/smart-lists/{smartList}/meals', [SmartListMealController::class, 'store'])->name('smart-lists.meals.store');
    Route::delete('/smart-lists/{smartList}/meals/{meal}', [SmartListMealController::class, 'destroy'])->name('smart-lists.meals.remove');
});

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::post('/meals', [\App\Http\Controllers\Api\DashboardController::class, 'storeMeal']);
    Route::put('/meals/{meal}', [\App\Http\Controllers\Api\DashboardController::class, 'updateMeal']);
    Route::delete('/meals/{meal}', [\App\Http\Controllers\Api\DashboardController::class, 'destroyMeal']);
});
