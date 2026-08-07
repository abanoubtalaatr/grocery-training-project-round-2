<?php

namespace App\Http\Controllers\Api;

use App\Actions\Notification\DeleteNotificationAction;
use App\Actions\Notification\GetNotificationsAction;
use App\Actions\Notification\GetNotificationsWithResourcesAction;
use App\Actions\Notification\MarkNotificationAction;
use App\Actions\Notification\NotificationStatsAction;
use App\Actions\Notification\ShowNotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMultipleNotificationsRequest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected GetNotificationsAction $getNotificationsAction,
        protected GetNotificationsWithResourcesAction $getNotificationsWithResourcesAction,
        protected NotificationStatsAction $notificationStatsAction,
        protected ShowNotificationAction $showNotificationAction,
        protected MarkNotificationAction $markNotificationAction,
        protected DeleteNotificationAction $deleteNotificationAction,
    ) {
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getNotificationsAction->execute($request),
        ]);
    }

    public function indexWithResources(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getNotificationsWithResourcesAction->execute($request),
        ]);
    }

    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationStatsAction->execute(),
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->showNotificationAction->execute($id),
        ]);
    }

    public function markAsRead(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->markNotificationAction->read($id),
        ]);
    }

    public function markAsUnread(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->markNotificationAction->unread($id),
        ]);
    }

    public function markAllAsRead()
    {
        return response()->json([
            'success' => true,
            'count' => $this->markNotificationAction->readAll(),
        ]);
    }

    public function destroy(string $id)
    {
        $this->deleteNotificationAction->destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }

public function destroyMultiple(DeleteMultipleNotificationsRequest$request)
{
    return response()->json([
        'success' => true,
        'deleted' => $this->deleteNotificationAction
            ->destroyMultiple($request->validated()['ids']),
    ]);
}
}