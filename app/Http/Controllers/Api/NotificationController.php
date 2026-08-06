<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Notification\DestroyNotificationAction;
use App\Actions\Api\Notification\GetNotificationsAction;
use App\Actions\Api\Notification\ShowNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiTrait;

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request, GetNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $notifications = $action->run($user, $request);

        return $this->dataResponse([
            'notifications' => NotificationResource::collection($notifications->getCollection()),
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ], 'Notifications retrieved successfully');
    }

    /**
     * Get a single notification
     */
    public function show(Request $request, string $id, ShowNotificationAction $action): JsonResponse
    {
        $notification = $action->run($request->user(), $id);

        return $this->dataResponse(new NotificationResource($notification, true), 'Notification retrieved successfully');
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id, DestroyNotificationAction $action): JsonResponse
    {
        $action->run($request->user(), $id);

        return $this->successResponse('Notification deleted successfully');
    }
}
