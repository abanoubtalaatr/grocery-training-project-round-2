<?php

namespace App\Action\Admin\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOrderStatusAction
{
    private array $statusFlow = [
        'placed' => ['processing', 'cancelled'],
        'processing' => ['shipping', 'cancelled'],
        'shipping' => ['out_for_delivery'],
        'out_for_delivery' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function execute(Order $order, string $newStatus, ?string $notes = null): void
    {
        $allowedTransitions = $this->statusFlow[$order->status] ?? [];

        if (!in_array($newStatus, $allowedTransitions)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot change status from '{$order->status}' to '{$newStatus}'"],
            ]);
        }

        DB::transaction(function () use ($order, $newStatus, $notes) {
            $updateData = ['status' => $newStatus];

            match ($newStatus) {
                'processing' => $updateData['processing_at'] = now(),
                'shipping' => $updateData['shipping_at'] = now(),
                'out_for_delivery' => $updateData['out_for_delivery_at'] = now(),
                'delivered' => $updateData['delivered_at'] = now(),
                'cancelled' => $updateData['cancelled_at'] = now(),
                default => null,
            };

            if ($notes) {
                $updateData['admin_notes'] = $notes;
            }

            $order->update($updateData);
        });
    }
}
