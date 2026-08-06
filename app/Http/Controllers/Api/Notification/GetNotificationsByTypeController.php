<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\GetNotificationsByTypeAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetNotificationsByTypeController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, string $type, GetNotificationsByTypeAction $action): JsonResponse
    {
        $notifications = $action->run($request->user(), $type);

        return $this->dataResponse([
            'type' => $type,
            'notifications' => NotificationResource::collection($notifications->getCollection()),
            'total' => $notifications->total(),
            'unread' => $notifications->whereNull('read_at')->count(),
        ], 'Notifications fetched by type successfully');
    }
}
