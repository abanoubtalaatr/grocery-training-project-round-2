<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\MarkNotificationAsUnreadAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

class MarkNotificationAsUnreadController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, string $id, MarkNotificationAsUnreadAction $action): JsonResponse
    {
        try {
            $notification = $action->run($request->user(), $id);

            return $this->dataResponse(new NotificationResource($notification), 'Notification marked as unread');
        } catch (LogicException $e) {
            return $this->errorResponse([], $e->getMessage(), 400);
        }
    }
}
