<?php

namespace App\Action\Api;

use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Validation\ValidationException;

class CreateStripeCheckoutSessionAction
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    public function execute($user, array $data): array
    {
        $order = Order::whereKey($data['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            throw ValidationException::withMessages([
                'order_id' => ['Order not found.'],
            ]);
        }

        try {
            $session = $this->checkoutService->createSessionForOrder($order, $user, (float) $data['amount']);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'checkout' => [$e->getMessage()],
            ]);
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
            'order_id' => $order->id,
        ];
    }
}