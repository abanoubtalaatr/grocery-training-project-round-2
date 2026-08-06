<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Action\Api\ListNotificationsAction;
use App\Action\Api\GetNotificationStatsAction;
use App\Action\Api\ShowNotificationAction;
use App\Action\Api\MarkNotificationAction;
use App\Action\Api\MarkAllNotificationsAsReadAction;
use App\Action\Api\DeleteNotificationAction;
use App\Action\Api\DestroyMultipleNotificationsAction;
use App\Action\Api\ClearNotificationsAction;
use App\Action\Api\ListNotificationsByTypeAction;
use App\Action\Api\GetUnreadCountAction;
use App\Action\Api\GetRecentNotificationsAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request, ListNotificationsAction $action): JsonResponse
    {
        $user = Auth::user();

        $notifications = $action->execute($user, $request, false);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->items(),
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ],
        ]);
    }

    /**
     * Same as index but attaches related models (meal, order) when referenced in notification data.
     */
    public function indexWithResources(Request $request, ListNotificationsAction $action): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $notifications = $action->execute($user, $request, true);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications->items(),
                    'unread_count' => $user->unreadNotifications()->count(),
                    'total_count' => $user->notifications()->count(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('notifications.with-resources failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /** Apply list filters to the authenticated user's notifications query. */
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * Get notification statistics
     */
    public function stats(GetNotificationStatsAction $action): JsonResponse
    {
        $user = Auth::user();

        $data = $action->execute($user);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get a single notification
     */
    public function show(string $id, ShowNotificationAction $action): JsonResponse
    {
        $user = Auth::user();

        $data = $action->execute($user, $id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id, MarkNotificationAction $action): JsonResponse
    {
        try {
            $user = Auth::user();

            $result = $action->execute($user, $id, 'read');

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $result['notification'],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(string $id, MarkNotificationAction $action): JsonResponse
    {
        try {
            $user = Auth::user();

            $result = $action->execute($user, $id, 'unread');

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread',
                'data' => $result['notification'],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(MarkAllNotificationsAsReadAction $action): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $action->execute($user);

        if ($unreadCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "{$unreadCount} notifications marked as read",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No unread notifications',
        ], 400);
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id, DeleteNotificationAction $action): JsonResponse
    {
        try {
            $user = Auth::user();
            $action->execute($user, $id);

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }
    }

    /**
     * Delete multiple notifications
     */
    public function destroyMultiple(Request $request, DestroyMultipleNotificationsAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $deletedCount = $action->execute($user, $request->ids);

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} notifications deleted successfully",
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll(Request $request, ClearNotificationsAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|string|in:read,unread,all',
            'confirmation' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $request->confirmation) {
            return response()->json([
                'success' => false,
                'message' => 'Please confirm you want to clear all notifications',
            ], 400);
        }

        $user = Auth::user();
        $type = $request->get('type', 'all');

        $count = $action->execute($user, $type);

        return response()->json([
            'success' => true,
            'message' => $type === 'all' ? "All {$count} notifications cleared" : "{$count} {$type} notifications cleared",
        ]);
    }

    /**
     * Get notifications by type
     */
    public function byType(string $type, ListNotificationsByTypeAction $action): JsonResponse
    {
        $user = Auth::user();

        $notifications = $action->execute($user, $type, 15);

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'notifications' => $notifications->items(),
                'total' => $notifications->total(),
                'unread' => $notifications->whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(GetUnreadCountAction $action): JsonResponse
    {
        $user = Auth::user();
        $count = $action->execute($user);

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'has_unread' => $count > 0,
            ],
        ]);
    }

    /**
     * Get recent notifications (last 24 hours)
     */
    public function recent(GetRecentNotificationsAction $action): JsonResponse
    {
        $user = Auth::user();

        $data = $action->execute($user, 10);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Transform notification for API response
     */
    private function transformNotification(DatabaseNotification $notification, bool $detailed = false): array
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

    /**
     * Get appropriate icon for notification type
     */
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
