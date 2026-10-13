<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeliveryPartnerAndTrackingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test delivery partner viewing assigned orders and milestone status progression.
     */
    public function test_delivery_partner_order_management(): void
    {
        $driver = User::create([
            'name' => 'Speedy Driver',
            'username' => 'driver_' . time() . rand(10, 99),
            'email' => 'driver_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_DELIVERY_PARTNER,
        ]);

        $otherDriver = User::create([
            'name' => 'Other Driver',
            'username' => 'otherdriver_' . time() . rand(10, 99),
            'email' => 'otherdriver_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_DELIVERY_PARTNER,
        ]);

        $customer = User::create([
            'name' => 'Order Customer',
            'username' => 'cust_' . time() . rand(10, 99),
            'email' => 'cust_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $address = Address::create([
            'user_id' => $customer->id,
            'label' => 'Home',
            'full_name' => 'Order Customer',
            'phone' => '+201012345678',
            'street_address' => '123 Nile Street',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'address_id' => $address->id,
            'driver_id' => $driver->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'processing',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 65.00,
            'placed_at' => now(),
        ]);

        // Non-assigned driver cannot view order details -> 403 Forbidden
        $this->actingAs($otherDriver, 'sanctum');
        $unauthorizedShow = $this->getJson("/api/driver/orders/{$order->id}");
        $unauthorizedShow->assertStatus(403);

        // Assigned driver lists orders
        $this->actingAs($driver, 'sanctum');
        $listResponse = $this->getJson('/api/driver/orders');
        $listResponse->assertStatus(200);

        $driverOrders = $listResponse->json('data.data');
        $this->assertNotEmpty($driverOrders);
        $this->assertSame($order->id, $driverOrders[0]['id']);

        // Assigned driver views order details
        $showResponse = $this->getJson("/api/driver/orders/{$order->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.address.latitude', '30.04440000');

        // Milestone transition 1: shipping
        $statusResp1 = $this->postJson("/api/driver/orders/{$order->id}/status", [
            'status' => 'shipping',
        ]);
        $statusResp1->assertStatus(200)
            ->assertJsonPath('data.status', 'shipping');

        $order->refresh();
        $this->assertSame('shipping', $order->status);
        $this->assertNotNull($order->shipping_at);

        // Milestone transition 2: out_for_delivery
        $statusResp2 = $this->postJson("/api/driver/orders/{$order->id}/status", [
            'status' => 'out_for_delivery',
        ]);
        $statusResp2->assertStatus(200)
            ->assertJsonPath('data.status', 'out_for_delivery');

        $order->refresh();
        $this->assertSame('out_for_delivery', $order->status);
        $this->assertNotNull($order->out_for_delivery_at);

        // Milestone transition 3: delivered
        $statusResp3 = $this->postJson("/api/driver/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);
        $statusResp3->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
    }

    /**
     * Test driver GPS location transmission and customer tracking payload.
     */
    public function test_driver_location_transmission_and_customer_tracking(): void
    {
        $driver = User::create([
            'name' => 'Tracker Driver',
            'username' => 'trackdriver_' . time() . rand(10, 99),
            'email' => 'trackdriver_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_DELIVERY_PARTNER,
        ]);

        $customer = User::create([
            'name' => 'Tracking Customer',
            'username' => 'custtrack_' . time() . rand(10, 99),
            'email' => 'custtrack_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $address = Address::create([
            'user_id' => $customer->id,
            'label' => 'Office',
            'full_name' => 'Tracking Customer',
            'phone' => '+201011112222',
            'street_address' => '45 Tahrir Square',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'address_id' => $address->id,
            'driver_id' => $driver->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'out_for_delivery',
            'subtotal' => 30.00,
            'tax' => 3.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 43.00,
            'placed_at' => now(),
            'out_for_delivery_at' => now(),
        ]);

        // Driver transmits GPS coordinates
        $this->actingAs($driver, 'sanctum');
        $locationResp = $this->postJson('/api/driver/location', [
            'latitude' => 30.0500,
            'longitude' => 31.2400,
            'order_id' => $order->id,
        ]);

        $locationResp->assertStatus(200)
            ->assertJsonPath('data.latitude', 30.05)
            ->assertJsonPath('data.longitude', 31.24);

        $order->refresh();
        $this->assertEqualsWithDelta(30.05, (float) $order->driver_latitude, 0.0001);
        $this->assertEqualsWithDelta(31.24, (float) $order->driver_longitude, 0.0001);
        $this->assertNotNull($order->driver_location_updated_at);

        // Customer tracks order
        $this->actingAs($customer, 'sanctum');
        $trackResp = $this->getJson('/api/orders/track');
        $trackResp->assertStatus(200);

        // Verify driver details, driver coordinates, and destination coordinates are included
        $this->assertSame($driver->id, $trackResp->json('data.tracking.driver.id'));
        $this->assertEqualsWithDelta(30.05, $trackResp->json('data.tracking.driver_location.latitude'), 0.0001);
        $this->assertEqualsWithDelta(31.24, $trackResp->json('data.tracking.driver_location.longitude'), 0.0001);
        $this->assertNotNull($trackResp->json('data.tracking.delivery_address'));
        $this->assertEqualsWithDelta(30.0444, $trackResp->json('data.tracking.delivery_address.latitude'), 0.0001);
    }
}
