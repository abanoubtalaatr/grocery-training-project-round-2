<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\DestroyMultipleNotificationsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DestroyMultipleNotificationsRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class DestroyMultipleNotificationsController extends Controller
{
    use ApiTrait;

    public function __invoke(DestroyMultipleNotificationsRequest $request, DestroyMultipleNotificationsAction $action): JsonResponse
    {
        $deletedCount = $action->run($request->user(), $request->validated('ids'));

        return $this->successResponse("{$deletedCount} notifications deleted successfully");
    }
}
