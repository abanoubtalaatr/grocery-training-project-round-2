<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Payment\FormatPaymentReceiptAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PaymentHistoryResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * Get payment history for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->where('status', '!=', 'cancelled')
            ->with(['items.meal.category', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            PaymentHistoryResource::collection($orders),
            'Payment history retrieved successfully'
        );
    }

    /**
     * Get receipt/invoice for a specific order.
     */
    public function show(Request $request, Order $order, FormatPaymentReceiptAction $action): JsonResponse
    {
        if ($order->user_id !== Auth::id()) {
            return $this->error('Order not found', 404);
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);
        $receipt = $action->execute($order);

        return $this->success(
            $receipt,
            'Receipt retrieved successfully'
        );
    }
}
