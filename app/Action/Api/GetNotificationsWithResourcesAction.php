<?php

namespace App\Action\Api;

use App\Models\Meal;
use App\Models\Order;

class GetNotificationsWithResourcesAction
{
    public function execute($user, array $filters = []): array
    {
        $action = new GetNotificationsAction();
        $result = $action->execute($user, $filters);

        $notifications = $result['notifications'];
        $pageItems = $notifications->getCollection();

        $mealIds = [];
        $orderIds = [];
        foreach ($pageItems as $notification) {
            $data = $this->notificationDataAsArray($notification->data);
            if (!empty($data['meal_id']) && is_numeric($data['meal_id'])) {
                $mealIds[] = (int) $data['meal_id'];
            }
            if (!empty($data['order_id']) && is_numeric($data['order_id'])) {
                $orderIds[] = (int) $data['order_id'];
            }
        }

        $mealIds = array_values(array_unique($mealIds));
        $orderIds = array_values(array_unique($orderIds));

        $meals = empty($mealIds) ? collect() : Meal::with('category')->whereIn('id', $mealIds)->get()->keyBy('id');
        $orders = empty($orderIds) ? collect() : Order::whereIn('id', $orderIds)->get()->keyBy('id');

        $transformed = $pageItems->map(function ($notification) use ($meals, $orders) {
            $data = $this->notificationDataAsArray($notification->data);
            $resources = [];

            if (!empty($data['meal_id']) && is_numeric($data['meal_id'])) {
                $meal = $meals->get((int) $data['meal_id']);
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

            if (!empty($data['order_id']) && is_numeric($data['order_id'])) {
                $order = $orders->get((int) $data['order_id']);
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

            $notification->resources = $resources;
            return $notification;
        })->values();

        $notifications->setCollection($transformed);

        return [
            'notifications' => $notifications->items(),
            'unread_count' => $result['unread_count'],
            'total_count' => $result['total_count'],
            'pagination' => $result['pagination'],
        ];
    }

    private function notificationDataAsArray($data): array
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
}