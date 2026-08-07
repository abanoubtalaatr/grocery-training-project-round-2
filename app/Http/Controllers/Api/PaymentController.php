<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetPaymentHistoryAction;
use App\Action\Api\GetOrderReceiptAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderReceiptResource;
use App\Http\Resources\Api\PaymentHistoryResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function paymentHistory(Request $request, GetPaymentHistoryAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return $this->success(
            [
                'payments' => PaymentHistoryResource::collection($result['orders']),
                'total_count' => $result['total_count'],
                'total_amount' => $result['total_amount'],
            ],
            'Payment history retrieved successfully'
        );
    }

    public function receipt(Request $request, Order $order, GetOrderReceiptAction $action): JsonResponse
    {
        $receipt = $action->execute($request->user(), $order);

        return $this->success(new OrderReceiptResource($receipt),'Receipt retrieved successfully');
    }

    public function invoice(Request $request, Order $order, GetOrderReceiptAction $action): JsonResponse
    {
        $receipt = $action->execute($request->user(), $order);
        $receipt['type'] = 'invoice';

        return $this->success(new OrderReceiptResource($receipt),'Invoice retrieved successfully');
    }
}
