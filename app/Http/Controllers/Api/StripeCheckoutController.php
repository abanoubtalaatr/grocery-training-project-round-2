<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateStripeCheckoutSessionRequest;
use App\Action\Stripe\CreateCheckoutSessionAction;
use App\Action\Stripe\VerifySessionAction;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    public function verifySession(Request $request, string $sessionId, VerifySessionAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user, $sessionId);

        if (! $result['status']) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['code'] ?? 400);
        }

        $order = $result['order'];

        return response()->json([
            'success' => true,
            'message' => 'Payment verified. Order is placed.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ],
        ]);
    }

    public function store(CreateStripeCheckoutSessionRequest $request, CreateCheckoutSessionAction $action): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $order = Order::query()->whereKey($data['order_id'])->where('user_id', $user->id)->first();
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $result = $action->handle($order, $user, (float) $data['amount']);

        if (! $result['status']) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['code'] ?? 500);
        }

        $session = $result['session'];

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return response()->json([
            'success' => true,
            'message' => 'Checkout session created. Open checkout_url in your WebView.',
            'data' => [
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'order_id' => $order->id,
            ],
        ]);
    }
}
