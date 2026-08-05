<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {

            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'active',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
            ]);

            // إضافة ميلز من 1 إلى 9
            foreach (range(11, 19) as $mealId) {

                $quantity = rand(1, 3);

                $meal = \App\Models\Meal::find($mealId);

                if (!$meal) {
                    continue;
                }

                $discount = 0;

                if ($meal->resolved_discount_price) {
                    $discount = ($meal->price - $meal->resolved_discount_price) * $quantity;
                }

                $cart->items()->create([
                    'meal_id' => $mealId,
                    'quantity' => $quantity,
                    'unit_price' => $meal->final_price,
                    'discount_amount' => $discount,
                    'subtotal' => $meal->final_price * $quantity,
                ]);
            }

            // تحديث إجماليات الكارت
            $cart->calculateTotals();
        }
    }
}