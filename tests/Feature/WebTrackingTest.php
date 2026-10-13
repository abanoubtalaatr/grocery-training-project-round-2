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

class WebTrackingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Order $order;
    protected Meal $meal;

    protected function setUp(): void
    {
        parent::setUp();

        Address::query()->delete();
        OrderItem::query()->delete();
        Order::query()->delete();

        $this->user = User::create([
            'username' => 'trackuser_' . Str::random(6),
            'email' => 'track_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Track',
            'lastname' => 'User',
        ]);

        $category = Category::create([
            'name' => 'Track Category',
            'slug' => 'track-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Track Meal',
            'slug' => 'track-meal-' . Str::random(6),
            'description' => 'A tracking test meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 20.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TRACK-' . strtoupper(uniqid()),
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'processing',
            'subtotal' => 40.00,
            'tax' => 4.00,
            'discount' => 0,
            'shipping_fee' => 5.00,
            'total' => 49.00,
            'placed_at' => now(),
            'estimated_delivery_time' => now()->addHours(24),
            'delivery_speed' => 'standard',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'meal_id' => $this->meal->id,
            'quantity' => 2,
            'unit_price' => 20.00,
            'discount_amount' => 0,
            'subtotal' => 40.00,
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_cannot_view_tracking_page(): void
    {
        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertRedirect();
    }

    public function test_user_can_view_tracking_page(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee(trans('orders.tracking.heading'));
    }

    public function test_user_cannot_track_other_users_order(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $this->actingAs($otherUser, 'web');

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(404);
    }

    public function test_tracking_page_shows_progress_bar(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.detail.status_placed'));
        $response->assertSee(trans('orders.detail.status_processing'));
        $response->assertSee(trans('orders.detail.status_shipping'));
        $response->assertSee(trans('orders.detail.status_out_for_delivery'));
        $response->assertSee(trans('orders.detail.status_delivered'));
    }

    public function test_tracking_page_shows_driver_info_when_assigned(): void
    {
        $driver = User::create([
            'username' => 'driver_' . Str::random(6),
            'email' => 'driver_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Driver',
            'lastname' => 'Person',
        ]);

        $this->order->update(['driver_id' => $driver->id]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.driver_name'));
        $response->assertSee('Driver');
    }

    public function test_tracking_page_shows_no_driver_when_unassigned(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.no_driver'));
    }

    public function test_tracking_page_shows_gps_unavailable_when_no_coordinates(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.gps_unavailable'));
    }

    public function test_tracking_page_shows_gps_available_when_coordinates_present(): void
    {
        $this->order->update([
            'driver_latitude' => 40.7128,
            'driver_longitude' => -74.0060,
            'driver_location_updated_at' => now(),
        ]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.live_tracking'));
    }

    public function test_tracking_page_shows_sla_status(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.estimated_delivery'));
        $response->assertSee($this->order->estimated_delivery_time->format('M d, Y'));
    }

    public function test_tracking_page_shows_delayed_when_sla_breached(): void
    {
        $this->order->update([
            'placed_at' => now()->subHours(25),
            'estimated_delivery_time' => now()->subHour(),
        ]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.delayed'));
    }

    public function test_tracking_page_shows_delivery_address(): void
    {
        $address = Address::create([
            'user_id' => $this->user->id,
            'label' => 'Home',
            'full_name' => 'Track User',
            'phone' => '+1234567890',
            'street_address' => '123 Main St',
            'building_number' => '1',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'US',
            'is_default' => true,
        ]);

        $this->order->update(['address_id' => $address->id]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.delivery_destination'));
        $response->assertSee('123 Main St');
    }

    public function test_tracking_page_shows_pickup_location(): void
    {
        $this->order->update([
            'delivery_type' => 'pickup',
            'pickup_location' => '123 Store Avenue',
            'address_id' => null,
        ]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.pickup_destination'));
        $response->assertSee('123 Store Avenue');
    }

    public function test_json_endpoint_returns_tracking_data(): void
    {
        $this->login();

        $response = $this->getJson(route('orders.track-json', $this->order->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'processing',
                'position' => 2,
                'is_sla_breached' => false,
            ],
        ]);
    }

    public function test_json_endpoint_returns_403_for_other_user(): void
    {
        $otherUser = User::create([
            'username' => 'jsonother_' . Str::random(6),
            'email' => 'jsonother_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'Json',
        ]);
        $this->actingAs($otherUser, 'web');

        $response = $this->getJson(route('orders.track-json', $this->order->id));

        $response->assertStatus(403);
    }

    public function test_tracking_page_shows_delivered_status(): void
    {
        $this->order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee(trans('orders.tracking.completed'));
    }

    public function test_tracking_page_shows_order_items_and_total(): void
    {
        $this->login();

        $response = $this->get(route('orders.track', $this->order->id));

        $response->assertStatus(200);
        $response->assertSee('Track Meal');
        $response->assertSee('$49.00');
    }
}
