<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\VerifyStripeSessionAction;
use App\Action\Api\CreateStripeCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripeCheckoutController extends Controller
{
    use \App\Traits\ApiResponse;

    public function __construct(private readonly \App\Services\StripeCheckoutService $checkoutService) {}

    public function verifySession(Request $request, string $sessionId, VerifyStripeSessionAction $action): JsonResponse
    {
        $user = $request->user();

        try {
            $order = $action->execute($user, $sessionId);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to verify payment session.', 502);
        }

        if (! $order) {
            return $this->error('Payment has not been completed or order not found.', 402);
        }

        return $this->success([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
        ], 'Payment verified. Order is placed.');
    }

    public function store(CreateStripeCheckoutSessionRequest $request, CreateStripeCheckoutSessionAction $action): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()->whereKey($data['order_id'])->where('user_id', $user->id)->first();
        if (! $order) {
            return $this->error('Order not found', 404);
        }

        try {
            $session = $action->execute($order, $user, (float) $data['amount']);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to start checkout. Please try again.', 502);
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $this->success([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
            'order_id' => $order->id,
        ], 'Checkout session created. Open checkout_url in your WebView.');
    }
}
