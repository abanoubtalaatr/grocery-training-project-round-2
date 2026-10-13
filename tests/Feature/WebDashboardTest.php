<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\MealView;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        OrderItem::query()->delete();
        Order::query()->delete();
        MealView::query()->delete();
        UserActivity::query()->delete();
        Favorite::query()->delete();

        $this->user = User::create([
            'username' => 'dashuser_' . Str::random(6),
            'email' => 'dash_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Dash',
            'lastname' => 'User',
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_cannot_view_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect();
    }

    public function test_user_can_view_dashboard(): void
    {
        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(trans('dashboard.title'));
        $response->assertSee(trans('dashboard.analytics.title'));
    }

    public function test_dashboard_shows_analytics(): void
    {
        $category = Category::create([
            'name' => 'Dash Category',
            'slug' => 'dash-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Dash Meal',
            'slug' => 'dash-meal-' . Str::random(6),
            'description' => 'A dashboard meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 10.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);

        MealView::create([
            'user_id' => $this->user->id,
            'meal_id' => $meal->id,
            'viewed_at' => now(),
        ]);

        Favorite::create([
            'user_id' => $this->user->id,
            'meal_id' => $meal->id,
        ]);

        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(trans('dashboard.analytics.viewed_products'));
        $response->assertSee(trans('dashboard.analytics.total_orders'));
        $response->assertSee(trans('dashboard.analytics.favorites'));
    }

    public function test_dashboard_shows_recent_orders(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-DASH-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
            'subtotal' => 25.00,
            'tax' => 2.50,
            'discount' => 0,
            'shipping_fee' => 5.00,
            'total' => 32.50,
            'placed_at' => now(),
        ]);

        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(trans('dashboard.recent_orders.title'));
        $response->assertSee($order->order_number);
    }

    public function test_dashboard_shows_shopping_insights(): void
    {
        Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-INS-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'delivered',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0,
            'shipping_fee' => 0,
            'total' => 55.00,
            'placed_at' => now()->subDays(5),
            'delivered_at' => now()->subDays(4),
        ]);

        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(trans('dashboard.shopping_insights.title'));
        $response->assertSee(trans('dashboard.shopping_insights.total_spent'));
    }

    public function test_dashboard_shows_empty_state_for_new_user(): void
    {
        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(trans('dashboard.recent_orders.no_orders'));
        $response->assertSee(trans('dashboard.top_purchases.no_purchases'));
    }

    public function test_dashboard_is_isolated_per_user(): void
    {
        $otherUser = User::create([
            'username' => 'otherdash_' . Str::random(6),
            'email' => 'otherdash_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'Dash',
        ]);

        Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-ISO-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
            'subtotal' => 100.00,
            'tax' => 10.00,
            'discount' => 0,
            'shipping_fee' => 5.00,
            'total' => 115.00,
            'placed_at' => now(),
        ]);

        $this->login();

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('ORD-ISO');
    }
}
