<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\GetOrdersAction;
use App\Actions\Order\TrackOrderAction;
use Exception;

class OrderController extends Controller
{
    public function show(Request $request, Order $order)
    {
        $order = $order->load(['items.meal', 'address']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => $this->formatOrder($order),
        ]);
    }
    
    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $order = $action->execute($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $this->formatOrder($order),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() === 'Failed to create order' ? 'Failed to create order' : $e->getMessage(),
                // Or you can map this correctly. For now we will return 400 for business logic errors.
            ], 400); 
        }
    }

    /**
     * Get all user orders.
     */
    public function index(Request $request, GetOrdersAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $orders = $action->execute($user)->map(function ($order) {
                return $this->formatOrder($order);
            });

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders,
                'total_count' => $orders->count(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track the last order with status positions.
     */
    public function track(Request $request, TrackOrderAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            $order = $action->execute($user);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active order found',
                ], 404);
            }

            if ($order->status === 'awaiting_payment') {
                return response()->json([
                    'success' => true,
                    'message' => 'Order is waiting for payment. Complete checkout to continue.',
                    'data' => [
                        'order' => $this->formatOrder($order),
                        'awaiting_payment' => true,
                        'tracking' => null,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order tracking retrieved successfully',
                'data' => [
                    'order' => $this->formatOrder($order),
                    'tracking' => [
                        'position' => $order->status_position,
                        'status' => $order->status,
                        'status_description' => $order->status_description,
                        'positions' => [
                            [
                                'position' => 1,
                                'status' => 'placed',
                                'label' => 'Order Placed',
                                'description' => 'Your order has been placed',
                                'completed' => in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->placed_at,
                            ],
                            [
                                'position' => 2,
                                'status' => 'processing',
                                'label' => 'Processing',
                                'description' => 'Your order is being processed',
                                'completed' => in_array($order->status, ['processing', 'shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->processing_at,
                            ],
                            [
                                'position' => 3,
                                'status' => 'shipping',
                                'label' => 'Shipping',
                                'description' => 'Your order is being shipped',
                                'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']),
                                'timestamp' => $order->shipping_at,
                            ],
                            [
                                'position' => 4,
                                'status' => 'out_for_delivery',
                                'label' => 'Out for Delivery',
                                'description' => 'Your order is on the way',
                                'completed' => in_array($order->status, ['out_for_delivery', 'delivered']),
                                'timestamp' => $order->out_for_delivery_at,
                            ],
                            [
                                'position' => 5,
                                'status' => 'delivered',
                                'label' => 'Delivered',
                                'description' => 'Your order has been delivered',
                                'completed' => $order->status === 'delivered',
                                'timestamp' => $order->delivered_at,
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format order data for response.
     */
    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
            'delivery_type' => $order->delivery_type,
            'status' => $order->status,
            'status_position' => $order->status_position,
            'status_description' => $order->status_description,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'meal' => [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'slug' => $item->meal->slug,
                        'image_url' => $item->meal->image_url,
                        ...$item->meal->getApiPriceAttributes(),
                        'category' => $item->meal->category ? [
                            'id' => $item->meal->category->id,
                            'name' => $item->meal->category->name,
                        ] : null,
                        'subcategory' => $item->meal->subcategory ? [
                            'id' => $item->meal->subcategory->id,
                            'name' => $item->meal->subcategory->name,
                        ] : null,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),
            'address' => $order->address ? [
                'id' => $order->address->id,
                'label' => $order->address->label,
                'full_name' => $order->address->full_name,
                'phone' => $order->address->phone,
                'country_code' => $order->address->country_code,
                'street_address' => $order->address->street_address,
                'building_number' => $order->address->building_number,
                'floor' => $order->address->floor,
                'apartment' => $order->address->apartment,
                'landmark' => $order->address->landmark,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
                'full_address' => $order->address->full_address,
                'latitude' => $order->address->latitude,
                'longitude' => $order->address->longitude,
            ] : null,
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'shipping_fee' => (float) ($order->shipping_fee ?? 0),
            'total' => $order->total,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'placed_at' => $order->placed_at,
            'processing_at' => $order->processing_at,
            'shipping_at' => $order->shipping_at,
            'out_for_delivery_at' => $order->out_for_delivery_at,
            'delivered_at' => $order->delivered_at,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'special_note' => $order->special_note,
            'schedule_delivery' => $order->schedule_delivery,
            'delivery_speed' => $order->delivery_speed,
        ];
    }
}
