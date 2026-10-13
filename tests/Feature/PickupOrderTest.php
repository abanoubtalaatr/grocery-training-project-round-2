<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PickupOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pickup_order_requires_location_and_confirmation(): void
    {
        $user = $this->createCustomerWithCart();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/orders', [
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'amount' => 11.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pickup_location', 'pickup_location_confirmed']);
    }

    public function test_customer_can_place_confirmed_pickup_order(): void
    {
        $pickupLocation = 'Downtown Grocery Pickup Counter';
        Setting::query()->delete();
        Setting::create([
            'site_name' => 'Grocery',
            'address' => 'Main Grocery Address',
            'store_address' => $pickupLocation,
        ]);

        $user = $this->createCustomerWithCart();

        $this->actingAs($user, 'sanctum');

        $settingsResponse = $this->getJson('/api/settings');
        $settingsResponse->assertStatus(200)
            ->assertJsonPath('data.pickup.location', $pickupLocation);

        $response = $this->postJson('/api/orders', [
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'pickup_location' => $pickupLocation,
            'pickup_location_confirmed' => true,
            'amount' => 11.00,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.delivery_type', 'pickup')
            ->assertJsonPath('data.address', null)
            ->assertJsonPath('data.pickup_location', $pickupLocation)
            ->assertJsonPath('data.pickup_location_confirmed', true)
            ->assertJsonPath('data.shipping_fee', 0);

        $order = Order::latest('id')->first();
        $this->assertSame('pickup', $order->delivery_type);
        $this->assertSame($pickupLocation, $order->pickup_location);
        $this->assertTrue($order->pickup_location_confirmed);
        $this->assertNull($order->address_id);
    }

    private function createCustomerWithCart(): User
    {
        $user = User::create([
            'name' => 'Pickup Customer',
            'username' => 'pickup_customer_' . uniqid(),
            'email' => 'pickup_customer_' . uniqid() . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $meal = Meal::query()->where('is_available', true)->where('stock_quantity', '>', 1)->first();

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
}
