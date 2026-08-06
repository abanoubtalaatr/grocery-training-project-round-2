<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ClearAllNotificationsAction;
use App\Action\Api\DeleteMultipleNotificationsAction;
use App\Action\Api\GetNotificationsAction;
use App\Action\Api\GetNotificationsWithResourcesAction;
use App\Action\Api\GetNotificationStatsAction;
use App\Action\Api\GetRecentNotificationsAction;
use App\Action\Api\GetUnreadCountAction;
use App\Action\Api\MarkAllNotificationsAsReadAction;
use App\Action\Api\ToggleNotificationReadStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClearAllNotificationsRequest;
use App\Http\Requests\Api\DeleteMultipleNotificationsRequest;
use App\Http\Resources\Api\NotificationResource;
use App\Http\Resources\Api\NotificationStatsResource;
use App\Http\Resources\Api\UnreadCountResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $request->all());

        return $this->success(
            [
                'notifications' => NotificationResource::collection($result['notifications']),
                'unread_count' => $result['unread_count'],
                'total_count' => $result['total_count'],
                'pagination' => $result['pagination'],
            ],
            'Notifications retrieved successfully'
        );
    }

    public function indexWithResources(Request $request, GetNotificationsWithResourcesAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $request->all());

        return $this->success(
            [
                'notifications' => $result['notifications'],
                'unread_count' => $result['unread_count'],
                'total_count' => $result['total_count'],
                'pagination' => $result['pagination'],
            ],
            'Notifications retrieved successfully'
        );
    }

    public function stats(Request $request, GetNotificationStatsAction $action): JsonResponse
    {
        $user = $request->user();
        $stats = $action->execute($user);

        return $this->success(new NotificationStatsResource($stats),'Notification statistics retrieved successfully');
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $this->success(new NotificationResource($notification, true),'Notification retrieved successfully');
    }

    public function markAsRead(string $id, Request $request, ToggleNotificationReadStatusAction $action): JsonResponse
    {
        $notification = $action->markAsRead($request->user(), $id);

        return $this->success(new NotificationResource($notification),'Notification marked as read');
    }

    public function markAsUnread(string $id, Request $request, ToggleNotificationReadStatusAction $action): JsonResponse
    {
        $notification = $action->markAsUnread($request->user(), $id);

        return $this->success(new NotificationResource($notification),'Notification marked as unread');
    }

    public function markAllAsRead(Request $request, MarkAllNotificationsAsReadAction $action): JsonResponse
    {
        $user = $request->user();
        $count = $action->execute($user);

        return $this->success(['marked_count' => $count],"{$count} notifications marked as read");
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }

    public function destroyMultiple(DeleteMultipleNotificationsRequest $request, DeleteMultipleNotificationsAction $action): JsonResponse
    {
        $deletedCount = $action->execute($request->user(), $request->ids);

        return $this->success(['deleted_count' => $deletedCount],"{$deletedCount} notifications deleted successfully");
    }

    public function clearAll(ClearAllNotificationsRequest $request, ClearAllNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $request->type ?? 'all');

        return $this->success(['cleared_count' => $result['count']],$result['message']);
    }

    public function byType(string $type, Request $request, GetNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $filters = array_merge($request->all(), ['type' => $type]);
        $result = $action->execute($user, $filters);

        return $this->success(
            [
                'type' => $type,
                'notifications' => NotificationResource::collection($result['notifications']),
                'total' => $result['total_count'],
                'unread' => $result['unread_count'],
            ],
            'Notifications retrieved successfully'
        );
    }

    public function unreadCount(Request $request, GetUnreadCountAction $action): JsonResponse
    {
        $user = $request->user();
        $count = $action->execute($user);

        return $this->success(new UnreadCountResource(['count' => $count]),'Unread count retrieved successfully');
    }

    public function recent(Request $request, GetRecentNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user);

        return $this->success(
            [
                'notifications' => NotificationResource::collection($result['notifications']),
                'total_recent' => $result['total_recent'],
                'unread_recent' => $result['unread_recent'],
            ],
            'Recent notifications retrieved successfully'
        );
    }
}
