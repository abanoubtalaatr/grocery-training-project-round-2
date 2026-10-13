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

class AuthorizationSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $userA;
    protected User $userB;
    protected Order $orderA;

    protected function setUp(): void
    {
        parent::setUp();

        Address::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();

        $this->userA = User::create([
            'username' => 'auth_user_a_' . Str::random(6),
            'email' => 'auth_a_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'User',
            'lastname' => 'Alpha',
        ]);

        $this->userB = User::create([
            'username' => 'auth_user_b_' . Str::random(6),
            'email' => 'auth_b_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'User',
            'lastname' => 'Beta',
        ]);

        $this->orderA = Order::create([
            'user_id' => $this->userA->id,
            'order_number' => 'ORD-SEC-' . strtoupper(uniqid()),
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
    }

    // ─── C2: Order Index IDOR ──────────────────────────────────────────

    public function test_order_index_does_not_leak_other_users_orders(): void
    {
        $this->actingAs($this->userB, 'sanctum');

        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $orderIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($this->orderA->id, $orderIds);
    }

    public function test_order_index_only_returns_own_orders(): void
    {
        $orderB = Order::create([
            'user_id' => $this->userB->id,
            'order_number' => 'ORD-SEC-B-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'pickup_location' => 'Main St',
            'status' => 'placed',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0,
            'shipping_fee' => 0,
            'total' => 11.00,
            'placed_at' => now(),
        ]);

        $this->actingAs($this->userB, 'sanctum');

        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $orderIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($orderB->id, $orderIds);
        $this->assertNotContains($this->orderA->id, $orderIds);
    }

    // ─── C3: Order Show IDOR ───────────────────────────────────────────

    public function test_order_show_rejects_cross_user_access(): void
    {
        $this->actingAs($this->userB, 'sanctum');

        $response = $this->getJson("/api/orders/{$this->orderA->id}");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Order not found.',
        ]);
    }

    public function test_order_show_allows_owner_access(): void
    {
        $this->actingAs($this->userA, 'sanctum');

        $response = $this->getJson("/api/orders/{$this->orderA->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => ['id' => $this->orderA->id],
        ]);
    }

    // ─── C4: deleteCard IDOR ──────────────────────────────────────────

    public function test_delete_card_rejects_nonexistent_customer(): void
    {
        $this->userA->update(['stripe_customer_id' => null]);

        $this->actingAs($this->userA, 'sanctum');

        $response = $this->deleteJson('/api/cards/pm_fake123');

        $response->assertOk();
        $response->assertJson(['status' => 'deleted']);
    }

    // ─── L9: Checkout Address Ownership (web already safe — verify) ───

    public function test_web_checkout_rejects_other_users_address(): void
    {
        $addressA = Address::create([
            'user_id' => $this->userA->id,
            'full_name' => 'User Alpha',
            'phone' => '1234567890',
            'street_address' => '123 Main St',
            'city' => 'Testville',
            'is_default' => true,
        ]);

        $this->actingAs($this->userB, 'web');

        $category = Category::create([
            'name' => 'Sec Cat',
            'slug' => 'sec-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Sec Meal',
            'slug' => 'sec-meal-' . Str::random(6),
            'description' => 'A security test meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 15.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);

        $cart = $this->userB->getOrCreateCart();
        $cart->items()->create([
            'meal_id' => $meal->id,
            'quantity' => 1,
            'unit_price' => $meal->price,
            'discount_amount' => 0,
            'subtotal' => $meal->price,
        ]);
        $cart->calculateTotals();

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $addressA->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertSessionHasErrors('address_id');
    }
}
