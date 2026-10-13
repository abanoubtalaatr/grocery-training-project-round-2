<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\DeliverySlaBreachedNotification;
use Carbon\CarbonInterface;

class DeliverySlaService
{
    public const SPEED_STANDARD = 'standard';
    public const SPEED_PRIORITY = 'priority';

    private const SLA_HOURS = [
        self::SPEED_STANDARD => 24,
        self::SPEED_PRIORITY => 2,
    ];

    public static function supportedSpeeds(): array
    {
        return array_keys(self::SLA_HOURS);
    }

    public function prepareForSave(Order $order): void
    {
        if ($order->delivery_type !== 'delivery') {
            if (! $order->exists || $order->isDirty('delivery_type')) {
                $order->delivery_speed = null;
                $order->estimated_delivery_time = null;
            }

            return;
        }

        $order->delivery_speed = $this->normalizeSpeed($order->delivery_speed);

        if (! $this->hasOrderConfirmationStarted($order)) {
            return;
        }

        if (! $order->placed_at) {
            $order->placed_at = now();
        }

        if (
            ! $order->estimated_delivery_time
            || $order->isDirty('delivery_type')
            || $order->isDirty('delivery_speed')
            || $order->isDirty('placed_at')
            || $order->isDirty('status')
        ) {
            $order->estimated_delivery_time = $this->deadlineFrom(
                $order->placed_at,
                $order->delivery_speed
            );
        }
    }

    public function normalizeSpeed(?string $speed): string
    {
        $normalized = strtolower(trim((string) $speed));

        return in_array($normalized, self::supportedSpeeds(), true)
            ? $normalized
            : self::SPEED_STANDARD;
    }

    public function deadlineFrom(CarbonInterface $startsAt, string $speed): CarbonInterface
    {
        return $startsAt->copy()->addHours(self::SLA_HOURS[$this->normalizeSpeed($speed)]);
    }

    public function notifyBreachedOrders(?CarbonInterface $now = null): int
    {
        $now ??= now();

        $orders = Order::query()
            ->with('user.notificationSettings')
            ->where('delivery_type', 'delivery')
            ->whereNotNull('estimated_delivery_time')
            ->where('estimated_delivery_time', '<=', $now)
            ->whereNotIn('status', ['awaiting_payment', 'delivered', 'cancelled'])
            ->get();

        $sent = 0;
        $notificationService = app(NotificationService::class);

        foreach ($orders as $order) {
            if (! $order->user || $this->hasBreachNotification($order)) {
                continue;
            }

            if ($notificationService->sendDeliverySlaBreached($order->user, $order)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function hasOrderConfirmationStarted(Order $order): bool
    {
        return $order->placed_at !== null
            || in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered'], true);
    }

    private function hasBreachNotification(Order $order): bool
    {
        return $order->user->notifications()
            ->where('data->type', DeliverySlaBreachedNotification::TYPE)
            ->where('data->order_id', $order->id)
            ->exists();
    }
}
