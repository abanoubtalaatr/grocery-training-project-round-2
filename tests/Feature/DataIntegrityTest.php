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
use App\Models\OrderNote;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
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
        OrderNote::query()->delete();
        CartItem::query()->delete();
        Cart::query()->delete();
        Address::query()->delete();

        $this->user = User::create([
            'username' => 'di_user_' . Str::random(6),
            'email' => 'di_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Data',
            'lastname' => 'Integrity',
        ]);

        $this->category = Category::create([
            'name' => 'DI Category',
            'slug' => 'di-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->meal = Meal::create([
            'category_id' => $this->category->id,
            'title' => 'DI Meal',
            'slug' => 'di-meal-' . Str::random(6),
            'description' => 'A test meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 10.00,
            'stock_quantity' => 20,
            'is_available' => true,
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    private function createAddress(): Address
    {
        return Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'country_code' => '+1',
            'street_address' => '123 Main St',
            'city' => 'Testville',
            'state' => 'TS',
            'country' => 'US',
        ]);
    }

    // ─── C12: Stock cannot go negative via order creation ─────────────

    public function test_stock_does_not_go_negative_when_ordering_full_stock(): void
    {
        $this->meal->update(['stock_quantity' => 3]);
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 3,
        ]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();

        $this->meal->refresh();
        $this->assertEquals(0, $this->meal->stock_quantity);
    }

    public function test_stock_decrement_matches_ordered_quantity(): void
    {
        $this->meal->update(['stock_quantity' => 50]);
        $orderQty = 3;
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => $orderQty,
        ]);

        $response = $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();

        $this->meal->refresh();
        $this->assertEquals(50 - $orderQty, $this->meal->stock_quantity);
    }

    // ─── C12: sold_count incremented on order ─────────────────────────

    public function test_sold_count_increments_on_order_creation(): void
    {
        $this->meal->update(['stock_quantity' => 50, 'sold_count' => 0]);
        $orderQty = 3;
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => $orderQty,
        ]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $this->meal->refresh();
        $this->assertEquals($orderQty, $this->meal->sold_count);
    }

    // ─── C12: Cancel restores stock ──────────────────────────────────

    public function test_cancellation_restores_stock(): void
    {
        $this->meal->update(['stock_quantity' => 50, 'sold_count' => 0]);
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 3,
        ]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);

        $this->meal->refresh();
        $this->assertEquals(47, $this->meal->stock_quantity);

        $this->post(route('orders.cancel', $order->id), [
            'reason' => 'Changed mind',
        ]);

        $this->meal->refresh();
        $this->assertEquals(50, $this->meal->stock_quantity);
    }

    // ─── C13: Cart item unique constraint enforced ───────────────────

    public function test_adding_same_meal_to_cart_increments_quantity_not_duplicates(): void
    {
        $this->login();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 2,
        ]);

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 3,
        ]);

        $cart = $this->user->getOrCreateCart();
        $cartItems = $cart->items()->where('meal_id', $this->meal->id)->get();

        $this->assertCount(1, $cartItems, 'Should have exactly one cart item, not duplicates');
        $this->assertEquals(5, $cartItems->first()->quantity);
    }

    public function test_cart_add_respects_stock_limit(): void
    {
        $this->meal->update(['stock_quantity' => 5]);
        $this->login();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 3,
        ]);

        $response = $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    // ─── M8: Single OrderNote per order ──────────────────────────────

    public function test_order_notes_field_saved_on_order(): void
    {
        $this->meal->update(['stock_quantity' => 50]);
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 1,
        ]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Extra instructions here',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Extra instructions here', $order->notes);
    }

    public function test_order_without_notes_has_null_notes(): void
    {
        $this->meal->update(['stock_quantity' => 50]);
        $this->login();
        $address = $this->createAddress();

        $this->post(route('cart.addItem'), [
            'meal_id' => $this->meal->id,
            'quantity' => 1,
        ]);

        $this->post(route('checkout.placeOrder'), [
            'delivery_type' => 'delivery',
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertNull($order->notes);
        $this->assertEquals(0, OrderNote::where('order_id', $order->id)->count());
    }

    // ─── M9: Indexes verified via migration inspection ───────────────

    public function test_cart_items_has_unique_constraint_on_cart_id_and_meal_id(): void
    {
        $indexes = Schema::getIndexes('cart_items');
        $found = false;
        foreach ($indexes as $index) {
            if (is_array($index['columns']) && in_array('cart_id', $index['columns']) && in_array('meal_id', $index['columns']) && ($index['unique'] ?? false)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'cart_items should have a unique constraint on (cart_id, meal_id)');
    }

    public function test_order_items_has_index_on_meal_id(): void
    {
        $indexes = Schema::getIndexes('order_items');
        $found = false;
        foreach ($indexes as $index) {
            if (is_array($index['columns']) && in_array('meal_id', $index['columns'])) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'order_items should have an index on meal_id');
    }

    public function test_meals_has_index_on_stock_quantity(): void
    {
        $indexes = Schema::getIndexes('meals');
        $found = false;
        foreach ($indexes as $index) {
            if (is_array($index['columns']) && in_array('stock_quantity', $index['columns'])) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'meals should have an index on stock_quantity');
    }

    // ─── M14: Cart calculateTotals correctness ───────────────────────

    public function test_cart_totals_calculation_with_coupon(): void
    {
        Offer::create([
            'title' => 'Save 10%',
            'code' => 'SAVE10',
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 5.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->login();

        $cart = $this->user->getOrCreateCart();
        $cart->items()->create([
            'meal_id' => $this->meal->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'discount_amount' => 0,
            'subtotal' => 20.00,
        ]);

        $cart->coupon_code = 'SAVE10';
        $cart->calculateTotals();
        $cart->refresh();

        // subtotal=20, coupon_discount=10% of 20 = 2.00, taxable=18, tax=1.80, total=19.80
        $this->assertEquals(20.00, (float) $cart->subtotal);
        $this->assertEquals(2.00, (float) $cart->coupon_discount);
        $this->assertEquals(1.80, (float) $cart->tax);
        $this->assertEquals(19.80, (float) $cart->total);
    }

    public function test_cart_totals_without_coupon(): void
    {
        $this->login();

        $cart = $this->user->getOrCreateCart();
        $cart->items()->create([
            'meal_id' => $this->meal->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'discount_amount' => 0,
            'subtotal' => 20.00,
        ]);

        $cart->calculateTotals();
        $cart->refresh();

        // subtotal=20, no coupon, tax=2.00, total=22.00
        $this->assertEquals(20.00, (float) $cart->subtotal);
        $this->assertEquals(0.00, (float) $cart->coupon_discount);
        $this->assertEquals(2.00, (float) $cart->tax);
        $this->assertEquals(22.00, (float) $cart->total);
    }

    public function test_cart_total_handles_empty_cart(): void
    {
        $this->login();

        $cart = $this->user->getOrCreateCart();
        $cart->calculateTotals();
        $cart->refresh();

        $this->assertEquals(0.00, (float) $cart->subtotal);
        $this->assertEquals(0.00, (float) $cart->tax);
        $this->assertEquals(0.00, (float) $cart->total);
    }
}
