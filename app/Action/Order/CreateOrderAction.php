<?php

// Update CreateOrderAction to dispatch SendEmailJob and CallingCompanyJob along with SendInvoiceJob

namespace App\Action\Order;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendInvoiceJob;
use App\Jobs\SendEmailJob;
use App\Jobs\CallingCompanyJob;
use Illuminate\Support\Facades\Log;

class CreateOrderAction
{
    public function handle($user, array $payload): Order
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $payload['address_id'],
                'payment_method' => $payload['payment_method'],
                'notes' => $payload['notes'] ?? null,
                'status' => 'processing',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($payload['items'] as $it) {
                $meal = \App\Models\Meal::select(['id', 'final_price'])->findOrFail($it['meal_id']);

                $order->items()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $it['quantity'],
                    'unit_price' => $meal->final_price,
                    'subtotal' => $meal->final_price * $it['quantity'],
                ]);

                $total += $meal->final_price * $it['quantity'];
            }

            $order->update(['total' => $total]);

            $cart = $user->cart;
            if ($cart) {
                $cart->items()->delete();
                $cart->calculateTotals();
            }

            DB::commit();

            // Dispatch jobs after commit
            try {
                SendInvoiceJob::dispatch($order->id)->onQueue('invoices');
            } catch (\Exception $e) {
                Log::error('Failed to dispatch SendInvoiceJob: ' . $e->getMessage());
            }

            try {
                SendEmailJob::dispatch($order->id)->onQueue('emails');
            } catch (\Exception $e) {
                Log::error('Failed to dispatch SendEmailJob: ' . $e->getMessage());
            }

            try {
                CallingCompanyJob::dispatch($order->id)->onQueue('external-calls');
            } catch (\Exception $e) {
                Log::error('Failed to dispatch CallingCompanyJob: ' . $e->getMessage());
            }

            return $order->load(['items.meal', 'address']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
