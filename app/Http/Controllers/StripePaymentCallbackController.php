<?php

namespace App\Http\Controllers;

use App\Action\HandleStripePaymentSuccessAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StripePaymentCallbackResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripePaymentCallbackController extends Controller
{
    use ApiResponse;

    public function success(Request $request, HandleStripePaymentSuccessAction $action): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing session_id parameter.',
                'data' => null,
            ], 400);
        }

        $result = $action->execute($sessionId);

        return $this->success(new StripePaymentCallbackResource($result),$result['message']);
    }

    public function cancel(Request $request): JsonResponse
    {
        $orderId = $request->query('order_id');

        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled.',
            'data' => ['order_id' => $orderId],
        ], 200);
    }
}
