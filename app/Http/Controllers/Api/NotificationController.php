<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClearAllNotificationsRequest;
use App\Http\Requests\Api\DeleteMultipleNotificationsRequest;
use App\Http\Resources\Api\NotificationResource;
use App\Models\Meal;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));

        $notifications = $this->buildNotificationsQuery($request)->paginate($perPage);
        $transformed = $notifications->getCollection()->map(fn ($n) => (new NotificationResource($n))->toArray($request))->values();
        $notifications->setCollection($transformed);

        return $this->success([
            'notifications' => $notifications->items(),
            'unread_count'  => $user->unreadNotifications()->count(),
            'total_count'   => $user->notifications()->count(),
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * Same as index but attaches related models (meal, order) when referenced in notification data.
     */
    public function indexWithResources(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user === null) {
            return $this->error('Unauthenticated', 401);
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));
        $notifications = $this->buildNotificationsQuery($request)->paginate($perPage);
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

        $transformed = $pageItems->map(function (DatabaseNotification $notification) use ($meals, $orders, $request) {
            $row = (new NotificationResource($notification))->toArray($request);
            $d = $this->notificationDataAsArray($notification->data);
            $resources = [];

            if (! empty($d['meal_id']) && is_numeric($d['meal_id'])) {
                $meal = $meals->get((int) $d['meal_id']);
                if ($meal) {
                    $resources['meal'] = [
                        'id'        => $meal->id,
                        'title'     => $meal->title,
                        'slug'      => $meal->slug,
                        'image_url' => $meal->image_url,
                        ...$meal->getApiPriceAttributes(),
                        'has_offer' => $meal->hasOffer(),
                        'category'  => $meal->category ? [
                            'id'   => $meal->category->id,
                            'name' => $meal->category->name,
                        ] : null,
                    ];
                }
            }

            if (! empty($d['order_id']) && is_numeric($d['order_id'])) {
                $order = $orders->get((int) $d['order_id']);
                if ($order) {
                    $resources['order'] = [
                        'id'           => $order->id,
                        'order_number' => $order->order_number,
                        'status'       => $order->status,
                        'total'        => (string) $order->total,
                        'placed_at'    => $order->placed_at?->toIso8601String(),
                        'created_at'   => $order->created_at?->toIso8601String(),
                    ];
                }
            }

            $row['resources'] = $resources;

            return $row;
        })->values();

        $notifications->setCollection($transformed);

        return $this->success([
            'notifications' => $notifications->items(),
            'unread_count'  => $user->unreadNotifications()->count(),
            'total_count'   => $user->notifications()->count(),
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    private function buildNotificationsQuery(Request $request)
    {
        $user = Auth::user();
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

    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $allNotifications = $user->notifications();
        $unreadNotifications = $user->unreadNotifications();

        $total = $allNotifications->count();
        $unread = $unreadNotifications->count();

        $typeCounts = $allNotifications->get()
            ->groupBy(function (DatabaseNotification $n) {
                $data = $this->notificationDataAsArray($n->data);

                return $data['type'] ?? 'unknown';
            })
            ->map(function ($notifications) {
                return [
                    'total'  => $notifications->count(),
                    'unread' => $notifications->whereNull('read_at')->count(),
                ];
            });

        $recentTypes = $allNotifications->latest()
            ->take(5)
            ->get()
            ->map(function (DatabaseNotification $n) {
                $data = $this->notificationDataAsArray($n->data);

                return $data['type'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $last = $allNotifications->latest()->first();

        return $this->success([
            'total'                => $total,
            'unread'               => $unread,
            'read'                 => max(0, $total - $unread),
            'by_type'              => $typeCounts,
            'recent_types'         => $recentTypes,
            'last_notification_at' => $last?->created_at?->toIso8601String(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return $this->success(new NotificationResource($notification, true));
    }

    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        if (! $notification->read_at) {
            $notification->markAsRead();

            return $this->success(new NotificationResource($notification), 'Notification marked as read');
        }

        return $this->error('Notification is already read', 400);
    }

    public function markAsUnread(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        if ($notification->read_at) {
            $notification->markAsUnread();

            return $this->success(new NotificationResource($notification), 'Notification marked as unread');
        }

        return $this->error('Notification is already unread', 400);
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();

        if ($unreadCount > 0) {
            $user->unreadNotifications()->update(['read_at' => now()]);

            return $this->success(null, "{$unreadCount} notifications marked as read");
        }

        return $this->error('No unread notifications', 400);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }

    public function destroyMultiple(DeleteMultipleNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $deletedCount = $user->notifications()
            ->whereIn('id', $request->input('ids'))
            ->delete();

        return $this->success(null, "{$deletedCount} notifications deleted successfully");
    }

    public function clearAll(ClearAllNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $type = $request->input('type', 'all');

        switch ($type) {
            case 'read':
                $count = $user->readNotifications()->count();
                $user->readNotifications()->delete();
                $message = "{$count} read notifications cleared";
                break;

            case 'unread':
                $count = $user->unreadNotifications()->count();
                $user->unreadNotifications()->delete();
                $message = "{$count} unread notifications cleared";
                break;

            case 'all':
            default:
                $count = $user->notifications()->count();
                $user->notifications()->delete();
                $message = "All {$count} notifications cleared";
                break;
        }

        return $this->success(null, $message);
    }

    public function byType(string $type, Request $request): JsonResponse
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->where('data->type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $transformedNotifications = $notifications->map(function ($notification) use ($request) {
            return (new NotificationResource($notification))->toArray($request);
        });

        return $this->success([
            'type'          => $type,
            'notifications' => $transformedNotifications,
            'total'         => $notifications->total(),
            'unread'        => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        return $this->success([
            'count'      => $count,
            'has_unread' => $count > 0,
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $user = Auth::user();

        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $transformedNotifications = $recentNotifications->map(function ($notification) use ($request) {
            return (new NotificationResource($notification))->toArray($request);
        });

        return $this->success([
            'notifications' => $transformedNotifications,
            'total_recent'  => $recentNotifications->count(),
            'unread_recent' => $recentNotifications->whereNull('read_at')->count(),
        ]);
    }
}
