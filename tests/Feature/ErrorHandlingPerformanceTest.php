<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ErrorHandlingPerformanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Category $category;
    protected Meal $meal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'nfrtest',
            'firstname' => 'NFR',
            'lastname' => 'Test',
            'email' => 'nfrtest@example.com',
            'phone' => '+966500000001',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Test Cat',
            'name_ar' => 'اختبار',
            'slug' => 'test-cat',
            'is_active' => true,
        ]);

        $this->meal = Meal::create([
            'title' => 'Test Meal',
            'title_ar' => 'وجبة اختبار',
            'description' => 'A test meal',
            'description_ar' => 'وجبة اختبار',
            'slug' => 'test-meal-nfr',
            'category_id' => $this->category->id,
            'image' => 'https://example.com/test.jpg',
            'price' => 25.00,
            'stock_quantity' => 100,
            'is_available' => true,
            'status' => 'active',
        ]);
    }

    // ─── H10: Exception messages don't leak internals ─────────────────

    public function test_meal_index_returns_paginated_results(): void
    {
        $response = $this->getJson('/api/meals');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'message', 'data', 'total_count',
            'current_page', 'per_page', 'last_page',
        ]);
    }

    public function test_meal_index_respects_per_page(): void
    {
        $response = $this->getJson('/api/meals?per_page=5');
        $response->assertStatus(200);
        $response->assertJsonPath('per_page', 5);
    }

    public function test_meal_index_caps_per_page(): void
    {
        $response = $this->getJson('/api/meals?per_page=999');
        $response->assertStatus(200);
        $response->assertJsonPath('per_page', 100);
    }

    public function test_meal_index_does_not_leak_sql_errors(): void
    {
        $response = $this->getJson('/api/meals?category_id=abc');
        $response->assertStatus(200);
        $body = $response->json();
        $jsonString = json_encode($body);
        $this->assertStringNotContainsString('SQL', $jsonString);
        $this->assertStringNotContainsString('Query', $jsonString);
        $this->assertStringNotContainsString('MySQL', $jsonString);
    }

    public function test_order_index_returns_paginated_results(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/orders');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'data', 'total_count',
            'current_page', 'per_page', 'last_page',
        ]);
    }

    public function test_payment_history_returns_paginated_results(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/payments/history');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'data', 'total_count', 'total_amount',
            'current_page', 'per_page', 'last_page',
        ]);
    }

    // ─── H12: Unbounded queries are bounded ───────────────────────────

    public function test_more_to_explore_has_limit(): void
    {
        $response = $this->getJson('/api/more-to-explore');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_slider_has_limit(): void
    {
        $response = $this->getJson('/api/sliders');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_new_products_has_limit(): void
    {
        $response = $this->getJson('/api/new-products');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_hot_meals_has_limit(): void
    {
        $response = $this->getJson('/api/meals/hot');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_today_deals_has_limit(): void
    {
        $response = $this->getJson('/api/meals/today');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    public function test_custom_limit_respected(): void
    {
        $response = $this->getJson('/api/more-to-explore?limit=3');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertLessThanOrEqual(3, count($data));
    }

    // ─── M19: Profile order_history is paginated ────────────────────

    public function test_profile_order_history_is_paginated(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/profile/?per_page=5');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'order_history' => [
                    'orders',
                    'ordered_at',
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
                'in_progress_orders',
            ],
        ]);
        $pagination = $response->json('data.order_history.pagination');
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('last_page', $pagination);
    }

    // ─── M15: Wishlist alert N+1 fix ─────────────────────────────────

    public function test_wishlist_alert_service_uses_batch_query(): void
    {
        $reflection = new \ReflectionClass(\App\Services\WishlistAlertService::class);
        $method = $reflection->getMethod('notifyWishlistUsers');
        $source = $method->getFileName();

        $content = file_get_contents($source);

        // Should use User::whereIn (batch) not User::find in a loop
        $this->assertStringContainsString('whereIn', $content, 'WishlistAlertService should use whereIn for batch queries');
    }

    // ─── M16: Contact email failure warning ──────────────────────────

    public function test_contact_controller_has_email_failure_tracking(): void
    {
        $reflection = new \ReflectionClass(\App\Http\Controllers\Api\ContactController::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('emailSent', $source, 'ContactController should track email failure');
        $this->assertStringContainsString('warning', $source, 'ContactController should return warning on email failure');
    }

    // ─── M17: stripe_customer_id hidden ───────────────────────────────

    public function test_user_model_hides_stripe_customer_id(): void
    {
        $user = new User();
        $hidden = $user->getHidden();
        $this->assertContains('stripe_customer_id', $hidden, 'User model should hide stripe_customer_id');
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    // ─── M7: Tax uses Setting model ───────────────────────────────────

    public function test_cart_totals_use_setting_tax_rate(): void
    {
        $reflection = new \ReflectionClass(\App\Models\Cart::class);
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('Setting::getSettings()', $source, 'Cart::calculateTotals should use Setting::getSettings()');
        $this->assertStringContainsString('tax_rate', $source, 'Cart::calculateTotals should use tax_rate from settings');
    }

    // ─── M13: Rate limiters exist for chatbot and contact ─────────────

    public function test_chatbot_rate_limiter_is_defined(): void
    {
        $this->assertTrue(RateLimiter::limiter('chatbot') !== null, 'chatbot rate limiter should be defined');
        $this->assertTrue(RateLimiter::limiter('contact') !== null, 'contact rate limiter should be defined');
    }

    // ─── H17: Order special note accessor fix ─────────────────────────

    public function test_order_model_uses_eager_loaded_notes(): void
    {
        $reflection = new \ReflectionClass(\App\Models\Order::class);
        $method = $reflection->getMethod('getSpecialNoteAttribute');
        $source = file_get_contents($method->getFileName());

        $this->assertStringContainsString('relationLoaded', $source, 'Order getSpecialNoteAttribute should use relationLoaded()');
    }

    // ─── Integration: MealController pagination ───────────────────────

    public function test_meal_search_with_filters_and_pagination(): void
    {
        $response = $this->getJson('/api/meals?search=Test&per_page=10&page=1');
        $response->assertStatus(200);
        $response->assertJsonPath('current_page', 1);
        $response->assertJsonPath('per_page', 10);
    }
}
