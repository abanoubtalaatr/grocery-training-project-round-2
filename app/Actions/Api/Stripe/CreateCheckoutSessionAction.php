<?php

namespace App\Actions\Api\Stripe;

use App\Models\Order;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Stripe\Checkout\Session;

class CreateCheckoutSessionAction
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    public function run(User $user, array $data): array
    {
        $order = Order::query()->whereKey($data['order_id'])->where('user_id', $user->id)->first();
        
        if (! $order) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Order not found.',
            ];
        }

        try {
            $session = $this->checkoutService->createSessionForOrder($order, $user, (float) $data['amount']);
            $order->update(['stripe_checkout_session_id' => $session->id]);

            return [
                'success' => true,
                'data' => [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'order_id' => $order->id,
                ],
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'status' => 422,
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'success' => false,
                'status' => 502,
                'message' => 'Unable to start checkout. Please try again.',
            ];
        }
    }
}
