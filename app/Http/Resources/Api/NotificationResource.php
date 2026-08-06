<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    protected bool $detailed = false;

    public function __construct($resource, bool $detailed = false)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
    }

    public function toArray(Request $request): array
    {
        $data = $this->notificationDataAsArray($this->data);
        $type = isset($data['type']) && is_string($data['type']) ? $data['type'] : 'unknown';

        $baseData = [
            'id' => $this->id,
            'type' => $type,
            'title' => is_string($data['title'] ?? null) ? $data['title'] : 'Notification',
            'body' => is_string($data['body'] ?? null) ? $data['body'] : '',
            'action_url' => $data['action_url'] ?? null,
            'action_label' => is_string($data['action_label'] ?? null) ? $data['action_label'] : 'View',
            'is_read' => ! is_null($this->read_at),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString() ?? '',
            'created_at_human' => $this->created_at?->diffForHumans() ?? '',
            'icon' => $this->getIconForType($type),
            'priority' => is_string($data['priority'] ?? null) ? $data['priority'] : 'normal',
        ];

        if ($this->detailed) {
            $baseData['data'] = $data;
            $baseData['channels'] = $data['channels'] ?? ['database'];
            $baseData['metadata'] = $data['metadata'] ?? [];
            $baseData['expires_at'] = $data['expires_at'] ?? null;
        }

        return $baseData;
    }

    private function notificationDataAsArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function getIconForType(string $type): string
    {
        $icons = [
            'order_confirmation' => 'shopping-bag',
            'order_shipped' => 'truck',
            'delivery_updates' => 'package',
            'out_of_stock_alerts' => 'alert-triangle',
            'weekly_discounts' => 'percent',
            'exclusive_member_offers' => 'crown',
            'seasonal_campaigns' => 'gift',
            'cart_reminders' => 'shopping-cart',
            'payment_billing' => 'credit-card',
            'system' => 'bell',
            'account' => 'user',
            'security' => 'shield',
        ];

        return $icons[$type] ?? 'bell';
    }
}