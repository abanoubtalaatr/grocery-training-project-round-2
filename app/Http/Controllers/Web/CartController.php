<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Meal;
use App\Models\Offer;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load(['items.meal.category', 'items.meal.subcategory']);

        $deliveryType = $request->query('delivery_type');
        $shippingFee = null;
        $totalWithShipping = null;

        if ($deliveryType && in_array($deliveryType, ['delivery', 'pickup'], true)) {
            $shippingService = app(ShippingService::class);
            $shippingFee = $shippingService->calculateShippingFee((float) $cart->subtotal, $deliveryType);
            $totalWithShipping = (float) $cart->total + $shippingFee;
        }

        return view('cart.show', [
            'cart' => $cart,
            'shippingFee' => $shippingFee,
            'totalWithShipping' => $totalWithShipping,
        ]);
    }

    public function addItem(Request $request): RedirectResponse
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $validated = $request->validate([
            'meal_id' => ['required', 'exists:meals,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
        ], [
            'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $meal = Meal::findOrFail($validated['meal_id']);

        if (! $meal->is_available) {
            return back()->withErrors(['meal_id' => 'This meal is currently unavailable.']);
        }

        if (! $meal->isInStock()) {
            return back()->withErrors(['meal_id' => 'This meal is out of stock.']);
        }

        if ($meal->stock_quantity < $validated['quantity']) {
            return back()->withErrors(['meal_id' => "Only {$meal->stock_quantity} items available in stock."]);
        }

        DB::beginTransaction();

        $lockedMeal = Meal::lockForUpdate()->find($meal->id);

        if ($lockedMeal->stock_quantity < $validated['quantity']) {
            DB::rollBack();
            return back()->withErrors(['meal_id' => "Only {$lockedMeal->stock_quantity} items available in stock."]);
        }

        $cartItem = $cart->items()->where('meal_id', $lockedMeal->id)->lockForUpdate()->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            $effectiveMax = min($maxPerProduct, $lockedMeal->stock_quantity);

            if ($newQuantity > $effectiveMax) {
                DB::rollBack();
                return back()->withErrors([
                    'quantity' => "Maximum {$maxPerProduct} units per product. You already have {$cartItem->quantity} in cart; maximum total is {$effectiveMax}.",
                ]);
            }

            if ($lockedMeal->stock_quantity < $newQuantity) {
                DB::rollBack();
                return back()->withErrors(['quantity' => "Only {$lockedMeal->stock_quantity} items available in stock."]);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $discountAmount = 0;
            if ($lockedMeal->resolved_discount_price) {
                $discountAmount = ($lockedMeal->price - $lockedMeal->resolved_discount_price) * $validated['quantity'];
            }

            $cart->items()->create([
                'meal_id' => $lockedMeal->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $lockedMeal->final_price,
                'discount_amount' => $discountAmount,
                'subtotal' => $lockedMeal->final_price * $validated['quantity'],
            ]);
        }

        $cart->calculateTotals();
        DB::commit();

        return back()->with('success', 'Item added to cart.');
    }

    public function updateItem(Request $request, string $itemId): RedirectResponse
    {
        $maxPerProduct = config('cart.max_quantity_per_product', 10);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxPerProduct],
        ], [
            'quantity.max' => "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        $cartItem = $cart->items()->where('id', $itemId)->lockForUpdate()->firstOrFail();
        $lockedMeal = Meal::lockForUpdate()->find($cartItem->meal_id);

        if ($lockedMeal->stock_quantity < $validated['quantity']) {
            DB::rollBack();
            return back()->withErrors(['quantity' => "Only {$lockedMeal->stock_quantity} items available in stock."]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);
        $cart->calculateTotals();

        DB::commit();

        return back()->with('success', 'Cart updated.');
    }

    public function removeItem(Request $request, string $itemId): RedirectResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cartItem = $cart->items()->findOrFail($itemId);

        DB::beginTransaction();

        $cartItem->delete();
        $cart->calculateTotals();

        DB::commit();

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();

        DB::beginTransaction();

        $cart->items()->delete();
        $cart->calculateTotals();

        DB::commit();

        return back()->with('success', 'Cart cleared.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $cart = $user->getOrCreateCart();
        $cart->load('items');

        if ($cart->isEmpty()) {
            return back()->withErrors(['code' => 'Cannot apply coupon to an empty cart.']);
        }

        $code = trim((string) $validated['code']);
        $offer = Offer::where('code', $code)->first();

        if (! $offer || ! $offer->isValid()) {
            return back()->withErrors(['code' => 'Invalid or expired coupon code.']);
        }

        $subtotal = (float) $cart->items->sum('subtotal');
        if (! $offer->canApplyToAmount($subtotal)) {
            return back()->withErrors([
                'code' => 'Minimum purchase of $' . number_format((float) $offer->minimum_purchase, 2) . ' required for this coupon.',
            ]);
        }

        $cart->coupon_code = $offer->code;
        $cart->calculateTotals();

        return back()->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cart = $user->getOrCreateCart();

        $cart->coupon_code = null;
        $cart->coupon_discount = 0;
        $cart->calculateTotals();

        return back()->with('success', 'Coupon removed.');
    }
}
