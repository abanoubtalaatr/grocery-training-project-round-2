<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\GetRecentNotificationsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetRecentNotificationsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetRecentNotificationsAction $action): JsonResponse
    {
        $recentNotifications = $action->run($request->user());

        return $this->dataResponse([
            'notifications' => NotificationResource::collection($recentNotifications),
            'total_recent' => $recentNotifications->count(),
            'unread_recent' => $recentNotifications->whereNull('read_at')->count(),
        ], 'Recent notifications fetched successfully');
    }
}
