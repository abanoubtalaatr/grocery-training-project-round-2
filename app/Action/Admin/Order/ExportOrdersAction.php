<?php

namespace App\Action\Admin\Order;

use App\Models\Order;

class ExportOrdersAction
{
    public function execute(array $filters): array
    {
        $query = Order::with(['user:id,username,email', 'items.meal']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $orders = $query->latest()->get();

        return $orders->map(fn ($order) => [
            'Order ID' => $order->id,
            'Order Number' => $order->order_number,
            'Customer' => $order->user?->full_name ?? 'N/A',
            'Email' => $order->user?->email ?? 'N/A',
            'Status' => $order->status,
            'Items Count' => $order->items->sum('quantity'),
            'Subtotal' => $order->subtotal,
            'Tax' => $order->tax,
            'Discount' => $order->discount,
            'Total' => $order->total,
            'Payment Method' => $order->payment_method,
            'Delivery Type' => $order->delivery_type,
            'Date' => $order->created_at?->format('Y-m-d H:i:s'),
        ])->toArray();
    }
}
