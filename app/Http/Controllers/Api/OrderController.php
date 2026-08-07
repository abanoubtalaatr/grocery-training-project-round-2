<?php

namespace App\Http\Controllers\Api;

use App\Actions\Order\StoreOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Get single order details.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.meal', 'address']);

        return $this->success(
            new OrderResource($order),
            'Order retrieved successfully'
        );
    }

    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request, StoreOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->user(), $request->validated());

        return $this->created(
            new OrderResource($order),
            'Order created successfully'
        );
    }

    /**
     * Get all user orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'     => true,
            'message'     => 'Orders retrieved successfully',
            'data'        => OrderResource::collection($orders),
            'total_count' => $orders->count(),
        ]);
    }

    /**
     * Track the last order with status positions.
     */
    public function track(Request $request): JsonResponse
    {
        $user = $request->user();

        $order = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $order) {
            return $this->notFound('No active order found');
        }

        if ($order->status === 'awaiting_payment') {
            return $this->success([
                'order'            => new OrderResource($order),
                'awaiting_payment' => true,
                'tracking'         => null,
            ], 'Order is waiting for payment. Complete checkout to continue.');
        }

        return $this->success([
            'order'    => new OrderResource($order),
            'tracking' => [
                'position'           => $order->status_position,
                'status'             => $order->status,
                'status_description' => $order->status_description,
                'positions'          => [
                    [
                        'position'    => 1,
                        'status'      => 'placed',
                        'label'       => 'Order Placed',
                        'description' => 'Your order has been placed',
                        'completed'   => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                        'timestamp'   => $order->placed_at,
                    ],
                    [
                        'position'    => 2,
                        'status'      => 'processing',
                        'label'       => 'Processing',
                        'description' => 'Your order is being processed',
                        'completed'   => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                        'timestamp'   => $order->processing_at,
                    ],
                    [
                        'position'    => 3,
                        'status'      => 'shipping',
                        'label'       => 'Shipping',
                        'description' => 'Your order is being shipped',
                        'completed'   => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                        'timestamp'   => $order->shipping_at,
                    ],
                    [
                        'position'    => 4,
                        'status'      => 'out_for_delivery',
                        'label'       => 'Out for Delivery',
                        'description' => 'Your order is on the way',
                        'completed'   => in_array($order->status, ['out_for_delivery', 'delivered']),
                        'timestamp'   => $order->out_for_delivery_at,
                    ],
                    [
                        'position'    => 5,
                        'status'      => 'delivered',
                        'label'       => 'Delivered',
                        'description' => 'Your order has been delivered',
                        'completed'   => $order->status === 'delivered',
                        'timestamp'   => $order->delivered_at,
                    ],
                ],
            ],
        ], 'Order tracking retrieved successfully');
    }
}
