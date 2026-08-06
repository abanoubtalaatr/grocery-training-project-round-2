<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetTodayDealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealTodayController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetTodayDealsAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'Today\'s deals retrieved successfully'
        );
    }
}
