<?php

namespace App\Action\Stripe;

use App\Models\Order;
use App\Services\StripeCheckoutService;
use Throwable;

class CreateCheckoutSessionAction
{
    public function __construct(private StripeCheckoutService $checkoutService)
    {
    }

    public function handle(Order $order, $user, float $amount)
    {
        try {
            $session = $this->checkoutService->createSessionForOrder($order, $user, $amount);

            return ['status' => true, 'session' => $session];
        } catch (\InvalidArgumentException $e) {
            return ['status' => false, 'code' => 422, 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            report($e);

            return ['status' => false, 'code' => 502, 'message' => 'Unable to start checkout. Please try again.'];
        }
    }
}
