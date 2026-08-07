<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Order\GetOrderTrackingAction;
use App\Actions\Api\Order\StoreOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Get all user orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => OrderResource::collection($orders),
            'total_count' => $orders->count(),
        ]);
    }

    /**
     * Get single order
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.meal', 'address']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Create a new order
     */
    public function store(StoreOrderRequest $request, StoreOrderAction $action): JsonResponse
    {
        $this->authorize('create', Order::class);

        $result = $action->execute($request);

        if (!$result['success']) {
            $statusCode = $result['status_code'] ?? 500;
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => new OrderResource($result['order']),
        ], 201);
    }

    /**
     * Track the last active order
     */
    public function track(Request $request, GetOrderTrackingAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        if (!$result['success']) {
            $statusCode = $result['status_code'] ?? 500;
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $statusCode);
        }

        $message = $result['awaiting_payment'] ?? false
            ? 'Order is waiting for payment. Complete checkout to continue.'
            : 'Order tracking retrieved successfully';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'order' => new OrderResource($result['order']),
                'awaiting_payment' => $result['awaiting_payment'] ?? false,
                'tracking' => $result['tracking'],
            ],
        ]);
    }
}
