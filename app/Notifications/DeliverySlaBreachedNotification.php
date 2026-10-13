<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\FcmPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeliverySlaBreachedNotification extends Notification
{
    use Queueable;

    public const TYPE = 'delivery_delayed';

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', FcmPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => self::TYPE,
            'title' => 'Delivery delayed',
            'body' => "Your order {$this->order->order_number} has passed its delivery SLA deadline.",
            'action_url' => "/orders/{$this->order->id}",
            'action_label' => 'View order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'delivery_speed' => $this->order->delivery_speed,
            'estimated_delivery_time' => $this->order->estimated_delivery_time?->toIso8601String(),
            'priority' => 'high',
        ];
    }
}
