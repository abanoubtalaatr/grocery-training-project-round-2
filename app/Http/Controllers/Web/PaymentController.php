<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function invoice(Request $request, Order $order): View
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'user']);

        $invoiceNumber = 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT);
        $paymentDisplay = match ($order->payment_method) {
            'card' => 'Credit/Debit Card',
            'stripe_checkout' => 'Card (Stripe Checkout)',
            'cash_on_delivery' => 'Cash on Delivery',
            default => ucfirst(str_replace('_', ' ', $order->payment_method)),
        };

        return view('payment.invoice', [
            'order' => $order,
            'invoiceNumber' => $invoiceNumber,
            'paymentDisplay' => $paymentDisplay,
        ]);
    }

    public function receipt(Request $request, Order $order): View
    {
        return $this->invoice($request, $order);
    }
}
