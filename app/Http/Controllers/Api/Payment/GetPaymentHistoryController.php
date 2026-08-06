<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Api\Payment\GetPaymentHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PaymentHistoryResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPaymentHistoryController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetPaymentHistoryAction $action): JsonResponse
    {
        try {
            $orders = $action->run($request->user());
            $paymentHistory = PaymentHistoryResource::collection($orders);

            return response()->json([
                'success' => true,
                'message' => 'Payment history retrieved successfully',
                'data' => $paymentHistory,
                'total_count' => $paymentHistory->count(),
                'total_amount' => (float) $orders->sum('total'),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to retrieve payment history', 500);
        }
    }
}
