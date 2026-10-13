<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\CancelOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\Cart;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller
{
    /**
     * Cancel an active order.
     */
    public function cancel(CancelOrderRequest $request, Order $order, CancelOrderAction $action): JsonResponse
    {
        try {
            $reason = $request->validated('reason');
            $cancelledOrder = $action->execute($request->user(), $order, is_string($reason) ? $reason : null);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $this->formatOrder($cancelledOrder),
            ]);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Order cancel failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $order = $order->load(['items.meal', 'address', 'notes.specialNote']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => $this->formatOrder($order),
        ]);
    }

    /**
     * Create a new order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Get user's active cart
            $cart = $user->activeCart()->with('items.meal')->first();

            if (! $cart || $cart->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add items to your cart before placing an order.',
                ], 400);
            }

            // Validate and process items from cart
            $itemsResult = $this->validateAndProcessCartItems($cart->items);
            if (! $itemsResult['success']) {
                return response()->json($itemsResult['response'], 400);
            }

            $items = $itemsResult['items'];

            // Calculate totals and shipping (use cart totals; add shipping for delivery)
            $cart->calculateTotals();
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $validated['delivery_type']);
            $totals = [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'discount' => $cart->discount,
                'coupon_code' => $cart->coupon_code,
                'shipping_fee' => $shippingFee,
                'total' => (float) $cart->total + $shippingFee,
            ];
            $total = $totals['total'];

            // Validate amount matches cart total
            if (abs($total - $validated['amount']) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount mismatch. Please recalculate your order.',
                    'calculated_total' => $total,
                    'provided_amount' => $validated['amount'],
                ], 400);
            }

            DB::beginTransaction();

            $stripePaymentIntentId = null;

            // Create order
            $order = $this->createOrder($user, $validated, $totals['subtotal'], $totals, $stripePaymentIntentId);

            // Create order items and update stock
            $this->createOrderItems($order, $items);

            // Clear user's active cart
            $this->clearUserCart($user);

            if (isset($validated['special_note_id']) || isset($validated['notes'])) {
                OrderNote::create([
                    'order_id' => $order->id,
                    'special_note_id' => $validated['special_note_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
            DB::commit();

            SendInvoiceEmailJob::dispatch($order);

            $order->load(['items.meal', 'address', 'notes.specialNote']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $this->formatOrder($order),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => 'Internal server error',
            ], 500);
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

            if (! $meal) {
                return [
                    'success' => false,
                    'response' => [
                        'success' => false,
                        'message' => 'One or more items in your cart are no longer available.',
                    ],
                ];
            }

            if (! $meal->is_available) {
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
     * Process payment for card orders.
     */
    private function processPayment($user, array $validated, float $total): array
    {
        if ($validated['payment_method'] !== 'card') {
            return ['success' => true];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if (! $user->stripe_customer_id) {
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
                'amount' => (int) ($total * 100),
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
                        'message' => 'Payment failed: '.$paymentIntent->status,
                    ],
                ];
            }

            return [
                'success' => true,
                'stripe_payment_intent_id' => $paymentIntent->id,
            ];
        } catch (\Exception $e) {
            Log::error('Payment processing failed', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'response' => [
                    'success' => false,
                    'message' => 'Payment processing failed',
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
            'coupon_code' => $totals['coupon_code'] ?? null,
            'payment_method_id' => null,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'delivery_type' => $validated['delivery_type'],
            'pickup_location' => $validated['delivery_type'] === 'pickup' ? $validated['pickup_location'] : null,
            'pickup_location_confirmed' => $validated['delivery_type'] === 'pickup',
            'delivery_speed' => $validated['delivery_type'] === 'delivery' ? ($validated['delivery_speed'] ?? null) : null,
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
            $meal = Meal::lockForUpdate()->find($item['meal']->id);

            if ($meal->stock_quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for '{$meal->title}'.");
            }

            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $meal->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'subtotal' => $item['subtotal'],
            ]);

            $meal->decrement('stock_quantity', $item['quantity']);
            $meal->increment('sold_count', $item['quantity']);
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
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

            $orders = Order::where('user_id', $user->id)
                ->with(['items.meal.category', 'items.meal.subcategory', 'address', 'notes.specialNote'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $formattedOrders = $orders->through(fn ($order) => $this->formatOrder($order));

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $formattedOrders->items(),
                'total_count' => $formattedOrders->total(),
                'current_page' => $formattedOrders->currentPage(),
                'per_page' => $formattedOrders->perPage(),
                'last_page' => $formattedOrders->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve orders', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Track the last order with status positions.
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $order = Order::where('user_id', $user->id)
                ->whereNotIn('status', ['cancelled', 'delivered'])
                ->with(['items.meal.category', 'items.meal.subcategory', 'address', 'driver', 'notes.specialNote'])
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $order) {
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
                        'driver' => $order->driver ? [
                            'id' => $order->driver->id,
                            'name' => $order->driver->full_name ?? $order->driver->username,
                            'phone' => $order->driver->phone,
                            'avatar' => $order->driver->profile_image_url,
                        ] : null,
                        'driver_location' => ($order->driver_latitude !== null && $order->driver_longitude !== null) ? [
                            'latitude' => (float) $order->driver_latitude,
                            'longitude' => (float) $order->driver_longitude,
                            'updated_at' => $order->driver_location_updated_at,
                        ] : null,
                        'delivery_address' => $order->address ? [
                            'full_address' => $order->address->full_address,
                            'latitude' => $order->address->latitude ? (float) $order->address->latitude : null,
                            'longitude' => $order->address->longitude ? (float) $order->address->longitude : null,
                        ] : null,
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
        } catch (\Exception $e) {
            Log::error('Failed to track order', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to track order',
                'error' => 'Internal server error',
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
            'pickup_location' => $order->pickup_location,
            'pickup_location_confirmed' => (bool) $order->pickup_location_confirmed,
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
            'driver' => $order->driver ? [
                'id' => $order->driver->id,
                'name' => $order->driver->full_name ?? $order->driver->username,
                'phone' => $order->driver->phone,
                'avatar' => $order->driver->profile_image_url,
            ] : null,
            'driver_location' => ($order->driver_latitude !== null && $order->driver_longitude !== null) ? [
                'latitude' => (float) $order->driver_latitude,
                'longitude' => (float) $order->driver_longitude,
                'updated_at' => $order->driver_location_updated_at,
            ] : null,
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
