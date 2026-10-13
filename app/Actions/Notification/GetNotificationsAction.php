<?php

namespace App\Actions\Notification;

use App\Models\Meal;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class GetNotificationsAction
{
    public function execute(Request $request)
    {
        $user = Auth::user();

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));

        $query = $user->notifications();

        if ($request->has('read')) {
            $request->boolean('read')
                ? $query->read()
                : $query->unread();
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

        $orderBy = in_array(
            $request->get('order_by', 'created_at'),
            $allowedOrderBy
        )
            ? $request->get('order_by')
            : 'created_at';

        $orderDirection = strtolower(
            $request->get('order_dir', 'desc')
        ) === 'asc'
            ? 'asc'
            : 'desc';

        $notifications = $query
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);

        $notifications->setCollection(
            $notifications->getCollection()->map(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => data_get($notification->data, 'type'),
                    'title' => data_get($notification->data, 'title'),
                    'body' => data_get($notification->data, 'body'),
                    'action_url' => data_get($notification->data, 'action_url'),
                    'action_label' => data_get($notification->data, 'action_label'),
                    'is_read' => ! is_null($notification->read_at),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
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