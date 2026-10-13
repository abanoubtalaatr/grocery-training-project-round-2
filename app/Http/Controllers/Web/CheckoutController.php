<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\ShippingService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService,
    ) {}

    public function show(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        if ($cart->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        $addresses = $user->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
        $settings = Setting::getSettings();
        $pickupLocation = $settings->store_address ?: $settings->address;

        return view('checkout.show', [
            'cart' => $cart,
            'addresses' => $addresses,
            'pickupLocation' => $pickupLocation,
            'settings' => $settings,
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_type' => ['required', 'in:delivery,pickup'],
            'address_id' => ['required_if:delivery_type,delivery', 'nullable', 'integer'],
            'pickup_location' => ['required_if:delivery_type,pickup', 'nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:stripe_checkout,cash_on_delivery'],
            'delivery_speed' => ['nullable', 'in:standard,priority'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $cart = $user->activeCart()->with('items.meal')->first();

        if (! $cart || $cart->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        if ($validated['delivery_type'] === 'delivery' && ! empty($validated['address_id'])) {
            $address = $user->addresses()->where('id', $validated['address_id'])->first();
            if (! $address) {
                return back()->withErrors(['address_id' => 'Invalid address selected.']);
            }
        }

        $itemsResult = $this->validateAndProcessCartItems($cart->items);
        if (! $itemsResult['success']) {
            return back()->withErrors(['cart' => $itemsResult['message']]);
        }

        $items = $itemsResult['items'];

        $cart->calculateTotals();
        $shippingService = app(ShippingService::class);
        $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $validated['delivery_type']);
        $total = (float) $cart->total + $shippingFee;

        DB::beginTransaction();

        $isHostedStripe = $validated['payment_method'] === 'stripe_checkout';

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $validated['delivery_type'] === 'delivery' ? ($validated['address_id'] ?? null) : null,
            'payment_method' => $validated['payment_method'],
            'coupon_code' => $cart->coupon_code,
            'payment_method_id' => null,
            'stripe_payment_intent_id' => null,
            'delivery_type' => $validated['delivery_type'],
            'pickup_location' => $validated['delivery_type'] === 'pickup' ? ($validated['pickup_location'] ?? null) : null,
            'pickup_location_confirmed' => $validated['delivery_type'] === 'pickup',
            'delivery_speed' => $validated['delivery_type'] === 'delivery' ? ($validated['delivery_speed'] ?? null) : null,
            'status' => $isHostedStripe ? 'awaiting_payment' : 'placed',
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'discount' => $cart->discount,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'notes' => $validated['notes'] ?? null,
            'placed_at' => $isHostedStripe ? null : now(),
        ]);

        $this->createOrderItems($order, $items);
        $this->clearUserCart($user);

        DB::commit();

        if ($isHostedStripe) {
            try {
                $session = $this->checkoutService->createSessionForOrder($order, $user, $total);
                $order->update(['stripe_checkout_session_id' => $session->id]);

                return redirect($session->url);
            } catch (\Exception $e) {
                report($e);
                return redirect()->route('cart.show')->with('error', 'Unable to start payment. Please try again.');
            }
        }

        return redirect()->route('payment.invoice', $order->id)
            ->with('success', 'Order placed successfully!');
    }

    private function validateAndProcessCartItems($cartItems): array
    {
        $items = [];

        foreach ($cartItems as $cartItem) {
            $meal = $cartItem->meal;

            if (! $meal) {
                return ['success' => false, 'message' => 'One or more items in your cart are no longer available.'];
            }

            if (! $meal->is_available) {
                return ['success' => false, 'message' => "Meal '{$meal->title}' is currently unavailable."];
            }

            if ($meal->stock_quantity < $cartItem->quantity) {
                return ['success' => false, 'message' => "Only {$meal->stock_quantity} items available for '{$meal->title}'."];
            }

            $maxPerProduct = config('cart.max_quantity_per_product', 10);
            if ($cartItem->quantity > $maxPerProduct) {
                return ['success' => false, 'message' => "Maximum {$maxPerProduct} units per product allowed for '{$meal->title}'."];
            }

            $items[] = [
                'meal' => $meal,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'discount_amount' => $cartItem->discount_amount,
                'subtotal' => $cartItem->subtotal,
            ];
        }

        return ['success' => true, 'items' => $items];
    }

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

    private function clearUserCart($user): void
    {
        $cart = $user->activeCart()->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['status' => 'completed']);
        }
    }
}
