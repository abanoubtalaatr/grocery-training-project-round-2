<?php

namespace App\Http\Controllers\Api\Notification;

use App\Actions\Api\Notification\GetNotificationStatsAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetNotificationStatsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetNotificationStatsAction $action): JsonResponse
    {
        $stats = $action->run($request->user());

        return $this->dataResponse($stats, 'Notification stats retrieved successfully');
    }
}
