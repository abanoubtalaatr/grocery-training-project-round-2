<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetMoreToExploreAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealMoreToExploreController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetMoreToExploreAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'More to explore retrieved successfully'
        );
    }
}
