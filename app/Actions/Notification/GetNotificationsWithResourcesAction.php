<?php

namespace App\Actions\Notification;

use App\Models\Meal;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class GetNotificationsWithResourcesAction
{
    public function execute(Request $request)
    {
        $user = Auth::user();

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));

        $notifications = $user->notifications()
            ->latest()
            ->paginate($perPage);

        $items = $notifications->getCollection();

        $mealIds = [];
        $orderIds = [];

        foreach ($items as $notification) {
            $data = is_array($notification->data)
                ? $notification->data
                : [];

            if (!empty($data['meal_id'])) {
                $mealIds[] = $data['meal_id'];
            }

            if (!empty($data['order_id'])) {
                $orderIds[] = $data['order_id'];
            }
        }

        $meals = Meal::with('category')
            ->whereIn('id', array_unique($mealIds))
            ->get()
            ->keyBy('id');

        $orders = Order::whereIn('id', array_unique($orderIds))
            ->get()
            ->keyBy('id');

        $notifications->setCollection(

            $items->map(function (DatabaseNotification $notification) use ($meals, $orders) {

                $data = is_array($notification->data)
                    ? $notification->data
                    : [];

                return [

                    'id' => $notification->id,

                    'type' => $data['type'] ?? null,

                    'title' => $data['title'] ?? null,

                    'body' => $data['body'] ?? null,

                    'is_read' => ! is_null($notification->read_at),

                    'created_at' => $notification->created_at,

                    'meal' => isset($data['meal_id'])
                        ? $meals->get($data['meal_id'])
                        : null,

                    'order' => isset($data['order_id'])
                        ? $orders->get($data['order_id'])
                        : null,

                ];
            })

        );

        return [

            'notifications' => $notifications,

            'unread_count' => $user->unreadNotifications()->count(),

            'total_count' => $user->notifications()->count(),

        ];
    }
}