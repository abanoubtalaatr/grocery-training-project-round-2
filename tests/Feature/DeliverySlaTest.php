<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeliverySlaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_standard_delivery_uses_24_hour_sla_deadline(): void
    {
        $now = Carbon::parse('2026-09-11 10:00:00');
        Carbon::setTestNow($now);

        $user = $this->createCustomerWithDeliveryCart();
        $address = $this->createAddressFor($user);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/orders', [
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'standard',
            'address_id' => $address->id,
            'amount' => 11.00,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.delivery_speed', 'standard');

        $deadline = Carbon::parse($response->json('data.estimated_delivery_time'));
        $this->assertTrue($deadline->equalTo($now->copy()->addHours(24)));

        Carbon::setTestNow();
    }

    public function test_priority_delivery_uses_2_hour_sla_deadline(): void
    {
        $now = Carbon::parse('2026-09-11 10:00:00');
        Carbon::setTestNow($now);

        $user = $this->createCustomerWithDeliveryCart();
        $address = $this->createAddressFor($user);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/orders', [
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'priority',
            'address_id' => $address->id,
            'amount' => 11.00,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.delivery_speed', 'priority');

        $deadline = Carbon::parse($response->json('data.estimated_delivery_time'));
        $this->assertTrue($deadline->equalTo($now->copy()->addHours(2)));

        Carbon::setTestNow();
    }

    public function test_invalid_delivery_speed_is_rejected(): void
    {
        $user = $this->createCustomerWithDeliveryCart();
        $address = $this->createAddressFor($user);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/orders', [
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'express',
            'address_id' => $address->id,
            'amount' => 11.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_speed']);
    }

    public function test_sla_enforcement_notifies_customer_once_after_deadline_passes(): void
    {
        $now = Carbon::parse('2026-09-11 12:00:00');
        Carbon::setTestNow($now);

        $user = $this->createCustomer();
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $this->createAddressFor($user)->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'priority',
            'status' => 'out_for_delivery',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 11.00,
            'placed_at' => $now->copy()->subHours(3),
        ]);
        $order->update(['estimated_delivery_time' => $now->copy()->subHour()]);

        $this->artisan('orders:enforce-delivery-sla')
            ->expectsOutput('Delivery SLA breach notifications sent: 1')
            ->assertSuccessful();

        $this->assertSame(1, $user->notifications()->where('data->type', 'delivery_delayed')->count());
        $this->assertSame($order->id, (int) $user->notifications()->first()->data['order_id']);

        $this->artisan('orders:enforce-delivery-sla')
            ->expectsOutput('Delivery SLA breach notifications sent: 0')
            ->assertSuccessful();

        $this->assertSame(1, $user->notifications()->where('data->type', 'delivery_delayed')->count());

        Carbon::setTestNow();
    }

    public function test_delivery_delay_alert_sends_when_sla_passed_and_order_undelivered(): void
    {
        $now = Carbon::parse('2026-09-11 12:00:00');
        Carbon::setTestNow($now);

        $user = $this->createCustomer();
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $this->createAddressFor($user)->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'standard',
            'status' => 'out_for_delivery',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 11.00,
            'placed_at' => $now->copy()->subHours(25),
            'estimated_delivery_time' => $now->copy()->subHour(),
        ]);

        $this->artisan('orders:enforce-delivery-sla')
            ->expectsOutput('Delivery SLA breach notifications sent: 1')
            ->assertSuccessful();

        $this->assertSame(1, $user->notifications()->where('data->type', 'delivery_delayed')->count());
        $this->assertSame($order->id, (int) $user->notifications()->first()->data['order_id']);

        Carbon::setTestNow();
    }

    public function test_delivery_delay_not_sent_when_order_delivered(): void
    {
        $now = Carbon::parse('2026-09-11 12:00:00');
        Carbon::setTestNow($now);

        $user = $this->createCustomer();
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $this->createAddressFor($user)->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'delivery_speed' => 'standard',
            'status' => 'delivered',
            'subtotal' => 10.00,
            'tax' => 1.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 11.00,
            'placed_at' => $now->copy()->subHours(25),
            'estimated_delivery_time' => $now->copy()->subHour(),
        ]);

        $this->artisan('orders:enforce-delivery-sla')
            ->expectsOutput('Delivery SLA breach notifications sent: 0')
            ->assertSuccessful();

        $this->assertSame(0, $user->notifications()->where('data->type', 'delivery_delayed')->count());

        Carbon::setTestNow();
    }

    private function createCustomerWithDeliveryCart(): User
    {
        $user = $this->createCustomer();
        $meal = $this->createMeal();

        $cart = Cart::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $cart->items()->create([
            'meal_id' => $meal->id,
            'quantity' => 1,
            'unit_price' => 10.00,
            'discount_amount' => 0,
            'subtotal' => 10.00,
        ]);

        $cart->calculateTotals();

        return $user;
    }

    private function createCustomer(): User
    {
        return User::create([
            'name' => 'SLA Customer',
            'username' => 'sla_customer_' . uniqid(),
            'email' => 'sla_customer_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    private function createAddressFor(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => 'SLA Customer',
            'phone' => '+201012345678',
            'street_address' => '123 Nile Street',
            'city' => 'Cairo',
        ]);
    }

    private function createMeal(): Meal
    {
        $category = Category::create([
            'name' => 'SLA Category ' . uniqid(),
            'slug' => 'sla-category-' . uniqid(),
        ]);

        return Meal::create([
            'category_id' => $category->id,
            'title' => 'SLA Test Product ' . uniqid(),
            'slug' => 'sla-test-product-' . uniqid(),
            'description' => 'SLA test product',
            'image' => 'test.jpg',
            'price' => 10.00,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);
    }
}
