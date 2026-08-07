<?php

namespace App\Actions\Admin;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderIndexAction
{
    public function execute(Request $request): array
    {
        $query = Order::with(['user', 'address', 'items.meal']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->input('delivery_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->input('date_to'));
        }

        $sortBy = $request->input('sort_by', 'placed_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'total') {
            $query->orderBy('total', $sortOrder);
        } elseif ($sortBy === 'customer') {
            $query->join('users', 'orders.user_id', '=', 'users.id')
                ->select('orders.*')
                ->orderBy('users.firstname', $sortOrder);
        } else {
            $query->orderBy('placed_at', $sortOrder);
        }

        $orders = $query->paginate(15)->withQueryString();

        $statuses = [
            'awaiting_payment' => 'Awaiting Payment',
            'placed' => 'Placed',
            'processing' => 'Processing',
            'shipping' => 'Shipping',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        $paymentMethods = ['card', 'paypal', 'apple_pay', 'google_pay', 'wallet'];
        $deliveryTypes = ['home_delivery', 'pickup'];

        return compact('orders', 'statuses', 'paymentMethods', 'deliveryTypes');
    }
}
