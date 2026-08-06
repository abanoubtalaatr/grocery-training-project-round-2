<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\GetUnreadNotificationsCountAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUnreadNotificationsCountController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetUnreadNotificationsCountAction $action): JsonResponse
    {
        $data = $action->run($request->user());

        return $this->dataResponse($data, 'Unread count fetched successfully');
    }
}
