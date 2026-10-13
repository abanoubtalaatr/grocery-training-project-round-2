<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Meal;
use App\Models\MealView;
use App\Models\Order;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Favorite;
use App\Models\Address;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_includes_analytics_metrics(): void
    {
        $user = User::create([
            'username' => 'analytics_user_' . uniqid(),
            'email' => 'analytics_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.analytics', fn ($analytics) => is_array($analytics))
            ->assertJsonPath('data.analytics.time_spent_minutes', fn ($val) => is_int($val))
            ->assertJsonPath('data.analytics.viewed_products_count', fn ($val) => is_int($val))
            ->assertJsonPath('data.analytics.orders_count', fn ($val) => is_int($val))
            ->assertJsonPath('data.analytics.notifications_count', fn ($val) => is_int($val))
            ->assertJsonPath('data.analytics.favorites_count', fn ($val) => is_int($val));
    }

    public function test_track_meal_view_creates_record(): void
    {
        $user = User::create([
            'username' => 'viewer_user_' . uniqid(),
            'email' => 'viewer_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
        ]);

        $meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal ' . uniqid(),
            'slug' => 'test-meal-' . uniqid(),
            'description' => 'Test meal',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson("/api/meals/{$meal->id}/view");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('meal_views', [
            'user_id' => $user->id,
            'meal_id' => $meal->id,
        ]);
    }

    public function test_viewed_products_count_includes_unique_meals(): void
    {
        $user = User::create([
            'username' => 'viewer_user_' . uniqid(),
            'email' => 'viewer_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
        ]);

        $meal1 = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal 1 ' . uniqid(),
            'slug' => 'test-meal-1-' . uniqid(),
            'description' => 'Test meal',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $meal2 = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal 2 ' . uniqid(),
            'slug' => 'test-meal-2-' . uniqid(),
            'description' => 'Test meal',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        // View meal1 twice, meal2 once
        MealView::create([
            'user_id' => $user->id,
            'meal_id' => $meal1->id,
            'viewed_at' => now()->subMinutes(10),
        ]);

        MealView::create([
            'user_id' => $user->id,
            'meal_id' => $meal1->id,
            'viewed_at' => now()->subMinutes(5),
        ]);

        MealView::create([
            'user_id' => $user->id,
            'meal_id' => $meal2->id,
            'viewed_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.analytics.viewed_products_count', 2); // Should count unique meals
    }

    public function test_track_user_activity_creates_or_updates_record(): void
    {
        $user = User::create([
            'username' => 'active_user_' . uniqid(),
            'email' => 'active_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $this->actingAs($user, 'sanctum');

        // First track - should create
        $response1 = $this->postJson('/api/activity');
        $response1->assertStatus(200);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
        ]);

        // Second track - should update existing
        $response2 = $this->postJson('/api/activity');
        $response2->assertStatus(200);

        $activities = UserActivity::where('user_id', $user->id)->get();
        $this->assertEquals(1, $activities->count()); // Should still be 1 record for today
    }

    public function test_time_spent_calculates_duration(): void
    {
        $user = User::create([
            'username' => 'active_user_' . uniqid(),
            'email' => 'active_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create activity with 30 minute duration
        UserActivity::create([
            'user_id' => $user->id,
            'session_start' => now()->subMinutes(30),
            'last_activity_at' => now(),
        ]);

        // Create activity with 15 minute duration
        UserActivity::create([
            'user_id' => $user->id,
            'session_start' => now()->subDays(1)->subMinutes(15),
            'last_activity_at' => now()->subDays(1),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.analytics.time_spent_minutes', 45); // 30 + 15
    }

    public function test_orders_count_excludes_cancelled(): void
    {
        $user = User::create([
            'username' => 'order_user_' . uniqid(),
            'email' => 'order_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '+201012345678',
            'street_address' => '123 Test St',
            'city' => 'Cairo',
        ]);

        Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 11.00,
        ]);

        Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'cancelled',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 11.00,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.analytics.orders_count', 1); // Only non-cancelled
    }

    public function test_notifications_count_unread_only(): void
    {
        $user = User::create([
            'username' => 'notif_user_' . uniqid(),
            'email' => 'notif_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $user->initializeNotificationSettings();

        // Create unread notification
        DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'test'],
            'read_at' => null,
        ]);

        // Create read notification
        DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'test'],
            'read_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.analytics.notifications_count', 1); // Only unread
    }

    public function test_favorites_count(): void
    {
        $user = User::create([
            'username' => 'fav_user_' . uniqid(),
            'email' => 'fav_user_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
        ]);

        $meal1 = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal 1 ' . uniqid(),
            'slug' => 'test-meal-1-' . uniqid(),
            'description' => 'Test meal',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $meal2 = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal 2 ' . uniqid(),
            'slug' => 'test-meal-2-' . uniqid(),
            'description' => 'Test meal',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'meal_id' => $meal1->id,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'meal_id' => $meal2->id,
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.analytics.favorites_count', 2);
    }
}
