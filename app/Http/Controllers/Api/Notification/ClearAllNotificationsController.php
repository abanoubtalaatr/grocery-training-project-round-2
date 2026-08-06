<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\ClearAllNotificationsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClearAllNotificationsRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ClearAllNotificationsController extends Controller
{
    use ApiTrait;

    public function __invoke(ClearAllNotificationsRequest $request, ClearAllNotificationsAction $action): JsonResponse
    {
        $message = $action->run($request->user(), $request->get('type', 'all'));

        return $this->successResponse($message);
    }
}
