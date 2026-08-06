<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\MarkAllNotificationsAsReadAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

class MarkAllNotificationsAsReadController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, MarkAllNotificationsAsReadAction $action): JsonResponse
    {
        try {
            $count = $action->run($request->user());

            return $this->successResponse("{$count} notifications marked as read");
        } catch (LogicException $e) {
            return $this->errorResponse([], $e->getMessage(), 400);
        }
    }
}
