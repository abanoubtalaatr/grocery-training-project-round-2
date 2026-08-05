<?php

namespace App\Http\Controllers\Api;

use App\Action\Payment\GetPaymentHistoryAction;
use App\Action\Payment\GetReceiptAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;

class PaymentController extends Controller
{
    public function paymentHistory(Request $request, GetPaymentHistoryAction $action): JsonResponse
    {
        $user = $request->user();

        $paymentHistory = $action->handle($user);

        $totalAmount = array_sum(array_map(fn($p) => $p['amount'] ?? 0, $paymentHistory->toArray()));

        return response()->json([
            'success' => true,
            'message' => 'Payment history retrieved successfully',
            'data' => $paymentHistory,
            'total_count' => $paymentHistory->count(),
            'total_amount' => (float) $totalAmount,
        ]);
    }

    public function receipt(Request $request, Order $order, GetReceiptAction $action): JsonResponse
    {
        $user = $request->user();

        $receipt = $action->handle($user, $order);

        return response()->json([
            'success' => true,
            'message' => 'Receipt retrieved successfully',
            'data' => $receipt,
        ]);
    }

    public function invoice(Request $request, Order $order, GetReceiptAction $action): JsonResponse
    {
        return $this->receipt($request, $order, $action);
    }
}
