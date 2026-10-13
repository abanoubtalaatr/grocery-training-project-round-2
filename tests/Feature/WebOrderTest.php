<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Address::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();

        $this->user = User::create([
            'username' => 'orderuser_' . Str::random(6),
            'email' => 'order_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'User',
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal',
            'slug' => 'test-meal-' . Str::random(6),
            'description' => 'A test meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 15.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
            'subtotal' => 30.00,
            'tax' => 3.00,
            'discount' => 0,
            'shipping_fee' => 5.00,
            'total' => 38.00,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'meal_id' => $meal->id,
            'quantity' => 2,
            'unit_price' => 15.00,
            'discount_amount' => 0,
            'subtotal' => 30.00,
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_cannot_view_orders(): void
    {
        $response = $this->get(route('orders.index'));

        $response->assertRedirect();
    }

    public function test_user_can_view_order_history(): void
    {
        $this->login();

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
    }

    public function test_order_history_shows_empty_state(): void
    {
        $this->user2 = User::create([
            'username' => 'noorders_' . Str::random(6),
            'email' => 'noorders_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'No',
            'lastname' => 'Orders',
        ]);
        $this->actingAs($this->user2, 'web');

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee("haven");
    }

    public function test_user_can_view_order_detail(): void
    {
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee('38.00');
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $this->actingAs($otherUser, 'web');

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(404);
    }

    public function test_guest_cannot_view_order_detail(): void
    {
        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertRedirect();
    }

    public function test_user_can_cancel_placed_order(): void
    {
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id), [
            'reason' => 'Changed my mind',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_can_cancel_without_reason(): void
    {
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_delivered_order(): void
    {
        $this->order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $this->order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id));

        $response->assertRedirect();
    }

    public function test_cannot_cancel_shipping_order(): void
    {
        $this->order->update(['status' => 'shipping', 'shipping_at' => now()]);
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'shipping',
        ]);
    }

    public function test_cannot_cancel_out_for_delivery_order(): void
    {
        $this->order->update(['status' => 'out_for_delivery', 'out_for_delivery_at' => now()]);
        $this->login();

        $response = $this->post(route('orders.cancel', $this->order->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'out_for_delivery',
        ]);
    }

    public function test_order_detail_shows_status_tracker(): void
    {
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Order Progress');
        $response->assertSee('Placed');
    }

    public function test_order_detail_shows_items(): void
    {
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Test Meal');
        $response->assertSee('2');
    }

    public function test_order_detail_shows_pricing(): void
    {
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('30.00');
        $response->assertSee('3.00');
        $response->assertSee('5.00');
        $response->assertSee('38.00');
    }

    public function test_cancelled_order_hides_cancel_button(): void
    {
        $this->order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertDontSee('Cancel Order');
    }

    public function test_delivered_order_hides_cancel_button(): void
    {
        $this->order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->login();

        $response = $this->get(route('orders.show', $this->order->id));

        $response->assertStatus(200);
        $response->assertDontSee('Cancel Order');
    }

    public function test_pagination_on_orders_page(): void
    {
        $this->login();

        for ($i = 0; $i < 15; $i++) {
            Order::create([
                'user_id' => $this->user->id,
                'order_number' => 'ORD-PAG-' . strtoupper(uniqid()) . $i,
                'payment_method' => 'cash_on_delivery',
                'delivery_type' => 'delivery',
                'status' => 'placed',
                'subtotal' => 10.00,
                'tax' => 1.00,
                'discount' => 0,
                'shipping_fee' => 5.00,
                'total' => 16.00,
                'placed_at' => now(),
            ]);
        }

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
    }
}
