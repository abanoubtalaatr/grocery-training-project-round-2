<?php

namespace App\Action\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Meal;
use App\Models\Order;
use Illuminate\Notifications\DatabaseNotification;

class ListNotificationsAction
{
    public function execute($user, Request $request, bool $withResources = false)
    {
        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));

        $query = $this->buildNotificationsQuery($user, $request);

        $notifications = $query->paginate($perPage);

        if (! $withResources) {
            $transformed = $notifications->getCollection()->map(fn ($n) => $this->transformNotification($n))->values();
            $notifications->setCollection($transformed);

            return $notifications;
        }

        // collect related ids
        $pageItems = $notifications->getCollection();

        $mealIds = [];
        $orderIds = [];
        foreach ($pageItems as $n) {
            $d = $this->notificationDataAsArray($n->data);
            if (! empty($d['meal_id']) && is_numeric($d['meal_id'])) {
                $mealIds[] = (int) $d['meal_id'];
            }
            if (! empty($d['order_id']) && is_numeric($d['order_id'])) {
                $orderIds[] = (int) $d['order_id'];
            }
        }

        $mealIds = array_values(array_unique($mealIds));
        $orderIds = array_values(array_unique($orderIds));

        $meals = $mealIds === []
            ? collect()
            : Meal::query()->with('category')->whereIn('id', $mealIds)->get()->keyBy('id');
        $orders = $orderIds === []
            ? collect()
            : Order::query()->whereIn('id', $orderIds)->get()->keyBy('id');

        $transformed = $pageItems->map(function (DatabaseNotification $notification) use ($meals, $orders) {
            $row = $this->transformNotification($notification);
            $d = $this->notificationDataAsArray($notification->data);
            $resources = [];

            if (! empty($d['meal_id']) && is_numeric($d['meal_id'])) {
                $meal = $meals->get((int) $d['meal_id']);
                if ($meal) {
                    $resources['meal'] = [
                        'id' => $meal->id,
                        'title' => $meal->title,
                        'slug' => $meal->slug,
                        'image_url' => $meal->image_url,
                        ...$meal->getApiPriceAttributes(),
                        'has_offer' => $meal->hasOffer(),
                        'category' => $meal->category ? [
                            'id' => $meal->category->id,
                            'name' => $meal->category->name,
                        ] : null,
                    ];
                }
            }

            if (! empty($d['order_id']) && is_numeric($d['order_id'])) {
                $order = $orders->get((int) $d['order_id']);
                if ($order) {
                    $resources['order'] = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total' => (string) $order->total,
                        'placed_at' => $order->placed_at?->toIso8601String(),
                        'created_at' => $order->created_at?->toIso8601String(),
                    ];
                }
            }

            $row['resources'] = $resources;

            return $row;
        })->values();

        $notifications->setCollection($transformed);

        return $notifications;
    }

    private function buildNotificationsQuery($user, Request $request)
    {
        $query = $user->notifications();

        if ($request->has('read')) {
            $isRead = filter_var($request->read, FILTER_VALIDATE_BOOLEAN);
            $query = $isRead ? $query->read() : $query->unread();
        }

        if ($request->has('type')) {
            $query->where('data->type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                  ->orWhere('data->body', 'like', "%{$search}%");
            });
        }

        $allowedOrderBy = ['created_at', 'read_at'];
        $orderBy = in_array((string) $request->get('order_by', 'created_at'), $allowedOrderBy, true)
            ? (string) $request->get('order_by', 'created_at')
            : 'created_at';
        $orderDirection = strtolower((string) $request->get('order_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query;
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

    private function transformNotification($notification, bool $detailed = false): array
    {
        $data = $this->notificationDataAsArray($notification->data);
        $type = isset($data['type']) && is_string($data['type']) ? $data['type'] : 'unknown';
        $baseData = [
            'id' => $notification->id,
            'type' => $type,
            'title' => is_string($data['title'] ?? null) ? $data['title'] : 'Notification',
            'body' => is_string($data['body'] ?? null) ? $data['body'] : '',
            'action_url' => $data['action_url'] ?? null,
            'action_label' => is_string($data['action_label'] ?? null) ? $data['action_label'] : 'View',
            'is_read' => ! is_null($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString() ?? '',
            'created_at_human' => $notification->created_at?->diffForHumans() ?? '',
            'icon' => $this->getIconForType($type),
            'priority' => is_string($data['priority'] ?? null) ? $data['priority'] : 'normal',
        ];

        if ($detailed) {
            $baseData['data'] = $data;
            $baseData['channels'] = $data['channels'] ?? ['database'];
            $baseData['metadata'] = $data['metadata'] ?? [];
            $baseData['expires_at'] = $data['expires_at'] ?? null;
        }

        return $baseData;
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
