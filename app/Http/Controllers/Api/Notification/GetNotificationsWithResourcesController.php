<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\GetNotificationsWithResourcesAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GetNotificationsWithResourcesController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetNotificationsWithResourcesAction $action): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user === null) {
                return $this->errorResponse([], 'Unauthenticated', 401);
            }

            $data = $action->run($user, $request);

            return $this->dataResponse($data, 'Notifications with resources retrieved successfully');
        } catch (Throwable $e) {
            return $this->errorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                'Failed to load notifications',
                500
            );
        }
    }
}
