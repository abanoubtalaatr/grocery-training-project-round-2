<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Notification\GetAdminNotificationsAction;
use App\Action\Admin\Notification\SendNotificationAction;
use App\Action\Admin\Notification\SendNotificationToAllAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendNotificationRequest;
use App\Http\Resources\Admin\AdminNotificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetAdminNotificationsAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success(
            [
                'notifications' => AdminNotificationResource::collection($result['notifications']),
                'pagination' => $result['pagination'],
            ],
            'Notifications retrieved successfully'
        );
    }

    public function send(SendNotificationRequest $request, SendNotificationAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->success($result,'Notification sent successfully',201);
    }

    public function sendToAll(SendNotificationRequest $request, SendNotificationToAllAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return $this->success($result,'Notification sent to all users successfully',201);
    }
}
