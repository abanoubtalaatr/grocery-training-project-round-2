<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateOrderAction;
use App\Action\Api\ShowOrderAction;
use App\Action\Api\ListOrdersAction;
use App\Action\Api\TrackOrderAction;
use App\Http\Resources\Api\OrderResource;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    use \App\Traits\ApiResponse;

    public function show(Request $request, Order $order, ShowOrderAction $action): JsonResponse
    {
        $order = $action->execute($order);

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
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

            return $this->success(new OrderResource($order), 'Order created successfully', 201);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Failed to create order', 500);
        }
    }

    /**
     * Validate and process order items from cart.
     */
    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];
        $subtotal = 0;

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (!$meal) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'One or more items in your cart are no longer available.',
                    ],
                ];
            }

            if (!$meal->is_available) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Meal '{$meal->title}' is currently unavailable",
                    ],
                ];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'",
                    ],
                ];
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => "Maximum {$maxPerProduct} units per product allowed. Please reduce quantity for '{$meal->title}'.",
                    ],
                ];
            }

            // Use cart item pricing (already calculated)
            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];

            $subtotal += $cartItem->subtotal;
        }

        return [
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Calculate order totals.
     */
    private function calculateTotals(float $subtotal): array
    {
        $tax = $subtotal * 0.1; // 10% tax
        $discount = 0;
        $total = $subtotal + $tax - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    /**
     * Process payment for card orders.
     */
    private function processPayment($user, array $validated, float $total): array
    {
        if ($validated['payment_method'] !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (!$user->stripe_customer_id) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Stripe customer not found. Please add a payment method first.',
                ],
            ];
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($total * 100),
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $validated['payment_method_id'],
                'off_session' => true,
                'confirm' => true,
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'Payment failed: ' . $paymentIntent->status,
                    ],
                ];
            }

            return [
                'success' => true,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Payment processing failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Create order record.
     */
    private function createOrder($user, array $validated, float $subtotal, array $totals, ?string $stripePaymentIntentId = null): Order
    {
        $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

        return Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['delivery_type'] === 'delivery' ? $validated['address_id'] : null,
            'payment_method' => $validated['payment_method'],
            'payment_method_id' => null,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'delivery_type' => $validated['delivery_type'],
            'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
            'subtotal' => $subtotal,
            'tax' => $totals['tax'],
            'discount' => $totals['discount'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
            'notes' => $validated['notes'] ?? null,
            'placed_at' => $isHostedStripe ? null : now(),
        ]);
    }

    /**
     * Create order items and update stock.
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $item['meal']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            $item['meal']->decrement('stock_quantity', $item['quantity']);
        }
    }

    /**
     * Clear user's active cart.
     */
    private function clearUserCart($user): void
    {
        $cart = $user->activeCart()->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }

    /**
     * Get all user orders.
     */
    public function index(Request $request, ListOrdersAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            $orders = $action->execute($user);

            return $this->success(\App\Http\Resources\Api\OrderResource::collection($orders)->values(), 'Orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve orders', 500);
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

            if (! $order) {
                return $this->error('No active order found', 404);
            }

            if ($order->status === 'awaiting_payment') {
                return $this->success([
                    'order' => (new \App\Http\Resources\Api\OrderResource($order))->toArray($request),
                    'awaiting_payment' => true,
                    'tracking' => null,
                ], 'Order is waiting for payment. Complete checkout to continue.');
            }

            $tracking = [
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
            ];

            return $this->success([
                'order' => (new \App\Http\Resources\Api\OrderResource($order))->toArray($request),
                'tracking' => $tracking,
            ], 'Order tracking retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to track order', 500);
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
