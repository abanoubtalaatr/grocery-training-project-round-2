<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebCartCheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Category $category;
    protected Meal $meal;

    protected function setUp(): void
    {
        parent::setUp();

        Meal::withoutEvents(function () {
            Meal::withTrashed()->forceDelete();
        });
        \App\Models\Subcategory::query()->delete();
        Category::withTrashed()->forceDelete();
        Offer::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();
        CartItem::query()->delete();
        Cart::query()->delete();
        Address::query()->delete();

        $this->user = User::create([
            'username' => 'cartuser_' . Str::random(6),
            'email' => 'cart_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'User',
        ]);

        $this->category = Category::create([
            'name' => 'Test Cat',
            'slug' => 'test-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->meal = Meal::create([
            'category_id' => $this->category->id,
            'title' => 'Test Meal',
            'slug' => 'test-meal-' . Str::random(6),
            'description' => 'A test meal description',
            'image' => 'https://example.com/image.jpg',
            'price' => 10.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    // ==================== Cart Tests ====================

    public function test_guest_cannot_view_cart(): void
    {
        $response = $this->get(route('cart.show'));

        $response->assertRedirect();
    }

    public function test_user_can_view_empty_cart(): void
    {
        $this->login();

        $response = $this->get(route('cart.show'));

        $response->assertStatus(200);
        $response->assertSee('Your cart is empty');
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $this->login();

        $response = $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'meal_id' => $this->meal->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_unavailable_meal_fails(): void
    {
        $this->meal->update(['is_available' => false]);
        $this->login();

        $response = $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('meal_id');
    }

    public function test_adding_out_of_stock_meal_fails(): void
    {
        $this->meal->update(['stock_quantity' => 0]);
        $this->login();

        $response = $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('meal_id');
    }

    public function test_adding_exceeding_stock_fails(): void
    {
        $this->login();

        $response = $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 100,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_adding_duplicate_meal_increments_quantity(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);
        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 2]);

        $cart = $this->user->getOrCreateCart();
        $cartItem = $cart->items()->where('meal_id', $this->meal->id)->first();
        $this->assertEquals(3, $cartItem->quantity);
    }

    public function test_user_can_update_cart_item(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $cart = $this->user->getOrCreateCart();
        $cartItem = $cart->items()->first();

        $response = $this->put(route('cart.update', $cartItem->id), ['quantity' => 5]);

        $response->assertRedirect();

        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_updating_to_exceeding_stock_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $cart = $this->user->getOrCreateCart();
        $cartItem = $cart->items()->first();

        $response = $this->put(route('cart.update', $cartItem->id), ['quantity' => 100]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_user_can_remove_cart_item(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $cart = $this->user->getOrCreateCart();
        $cartItem = $cart->items()->first();

        $response = $this->delete(route('cart.remove', $cartItem->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_user_can_clear_cart(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->delete(route('cart.clear'));

        $response->assertRedirect();
        $cart = $this->user->getOrCreateCart();
        $this->assertEquals(0, $cart->items()->count());
    }

    // ==================== Coupon Tests ====================

    public function test_user_can_apply_valid_coupon(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $offer = Offer::create([
            'title' => 'Test Offer',
            'code' => 'TEST10',
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $response = $this->post(route('cart.applyCoupon'), ['code' => 'TEST10']);

        $response->assertRedirect();

        $cart = $this->user->getOrCreateCart();
        $this->assertEquals('TEST10', $cart->coupon_code);
    }

    public function test_applying_invalid_coupon_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->post(route('cart.applyCoupon'), ['code' => 'INVALID']);

        $response->assertSessionHasErrors('code');
    }

    public function test_applying_expired_coupon_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        Offer::create([
            'title' => 'Expired Offer',
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'discount_value' => 10,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
            'is_active' => true,
        ]);

        $response = $this->post(route('cart.applyCoupon'), ['code' => 'EXPIRED']);

        $response->assertSessionHasErrors('code');
    }

    public function test_applying_coupon_below_minimum_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        Offer::create([
            'title' => 'High Min',
            'code' => 'HIGHMIN',
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 1000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $response = $this->post(route('cart.applyCoupon'), ['code' => 'HIGHMIN']);

        $response->assertSessionHasErrors('code');
    }

    public function test_user_can_remove_coupon(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $offer = Offer::create([
            'title' => 'Test Offer',
            'code' => 'REMOVE10',
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->post(route('cart.applyCoupon'), ['code' => 'REMOVE10']);
        $response = $this->delete(route('cart.removeCoupon'));

        $response->assertRedirect();

        $cart = $this->user->getOrCreateCart();
        $this->assertNull($cart->coupon_code);
    }

    // ==================== Checkout Tests ====================

    public function test_empty_cart_redirects_from_checkout(): void
    {
        $this->login();

        $response = $this->get(route('checkout.show'));

        $response->assertRedirect(route('cart.show'));
    }

    public function test_user_can_view_checkout_with_items(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('checkout');
    }

    public function test_checkout_shows_addresses(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('123 Main St');
    }

    public function test_checkout_shows_pickup_location(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('pickup');
    }

    // ==================== Order Placement Tests ====================

    public function test_user_can_place_cod_delivery_order(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 2]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_speed' => 'standard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'placed',
        ]);

        $this->meal->refresh();
        $this->assertEquals(48, $this->meal->stock_quantity);
    }

    public function test_user_can_place_cod_pickup_order(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'pickup',
            'pickup_location' => 'Store Address',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'status' => 'placed',
        ]);
    }

    public function test_order_without_address_for_delivery_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertSessionHasErrors('address_id');
    }

    public function test_order_with_invalid_address_fails(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => 99999,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertSessionHasErrors('address_id');
    }

    public function test_order_clears_cart(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $cart = $this->user->getOrCreateCart();
        $this->assertEquals(0, $cart->items()->count());
    }

    public function test_order_reduces_stock(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 3]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $this->meal->refresh();
        $this->assertEquals(47, $this->meal->stock_quantity);
    }

    public function test_order_with_coupon_applies_discount(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        Offer::create([
            'title' => 'Test Offer',
            'code' => 'SAVE10',
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 2]);
        $this->post(route('cart.applyCoupon'), ['code' => 'SAVE10']);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('SAVE10', $order->coupon_code);
        $this->assertGreaterThan(0, $order->discount);
    }

    public function test_order_with_priority_delivery(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_speed' => 'priority',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals('priority', $order->delivery_speed);
    }

    // ==================== Invoice Tests ====================

    public function test_user_can_view_invoice(): void
    {
        $this->login();

        $order = Order::create([
            'user_id' => $this->user->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'pickup_location' => 'Store',
            'pickup_location_confirmed' => true,
            'status' => 'placed',
            'subtotal' => 10,
            'tax' => 1,
            'discount' => 0,
            'shipping_fee' => 0,
            'total' => 11,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'meal_id' => $this->meal->id,
            'quantity' => 1,
            'unit_price' => 10,
            'discount_amount' => 0,
            'subtotal' => 10,
        ]);

        $response = $this->get(route('payment.invoice', $order->id));

        $response->assertStatus(200);
        $response->assertSee('INV-');
        $response->assertSee($order->order_number);
    }

    public function test_user_cannot_view_other_users_invoice(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $order = Order::create([
            'user_id' => $otherUser->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'pickup_location' => 'Store',
            'pickup_location_confirmed' => true,
            'status' => 'placed',
            'subtotal' => 10,
            'tax' => 1,
            'total' => 11,
            'placed_at' => now(),
        ]);

        $this->login();

        $response = $this->get(route('payment.invoice', $order->id));

        $response->assertStatus(404);
    }

    public function test_order_notes_are_saved(): void
    {
        $this->login();

        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'is_default' => true,
        ]);

        $this->post(route('cart.addItem'), ['meal_id' => $this->meal->id, 'quantity' => 1]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Leave at the door please',
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals('Leave at the door please', $order->notes);
    }
}
