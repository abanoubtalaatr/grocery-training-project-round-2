<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevelopmentOrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@example.test')->first();
        $driver = User::where('email', 'driver@example.test')->first();

        if (! $customer || ! $driver) {
            $this->command->warn('Skipping order seeding: customer or driver user not found.');

            return;
        }

        $address = Address::where('user_id', $customer->id)->first();
        $meal = Meal::where('is_available', true)->first();

        if (! $meal) {
            $this->command->warn('Skipping order seeding: no available meals found.');

            return;
        }

        $quantity = 2;
        $unitPrice = (float) $meal->price;
        $subtotal = round($unitPrice * $quantity, 2);
        $tax = round($subtotal * 0.1, 2);
        $shippingFee = 5.00;
        $total = round($subtotal + $tax + $shippingFee, 2);

        $order = Order::updateOrCreate(
            ['order_number' => 'DEV-ORDER-001'],
            [
                'user_id' => $customer->id,
                'address_id' => $address?->id,
                'driver_id' => $driver->id,
                'payment_method' => 'cash_on_delivery',
                'delivery_type' => 'delivery',
                'status' => 'out_for_delivery',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => 0,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'placed_at' => now()->subHours(2),
                'processing_at' => now()->subHours(1),
                'shipping_at' => now()->subMinutes(30),
                'out_for_delivery_at' => now()->subMinutes(10),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'meal_id' => $meal->id],
            [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => 0,
                'subtotal' => $subtotal,
            ]
        );

        $this->seedMessages($order, $customer, $driver);

        $this->command->info('Development order seeded: ' . $order->order_number);
    }

    private function seedMessages(Order $order, User $customer, User $driver): void
    {
        OrderMessage::updateOrCreate(
            ['order_id' => $order->id, 'sender_id' => $customer->id, 'body' => 'Hi, I am waiting for the delivery.'],
            ['read_at' => now()]
        );

        OrderMessage::updateOrCreate(
            ['order_id' => $order->id, 'sender_id' => $driver->id, 'body' => 'On my way! Almost there.'],
            ['read_at' => null]
        );
    }
}
