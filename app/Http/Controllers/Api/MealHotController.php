<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetHotMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealHotController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetHotMealsAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'Hot meals retrieved successfully'
        );
    }
}
