<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Dashboard\GetDashboardAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DashboardResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiTrait;

    /**
     * Get dashboard statistics and insights.
     */
    public function __invoke(Request $request, GetDashboardAction $action): JsonResponse
    {
        $data = $action->run($request->user());
        return $this->dataResponse(new DashboardResource($data), 'Dashboard data retrieved successfully');
    }
}
