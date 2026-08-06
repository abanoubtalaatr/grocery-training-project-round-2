<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetMealRecommendationsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealRecommendationResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealRecommendationsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetMealRecommendationsAction $action): JsonResponse
    {
        $limit = $request->input('limit', 10);
        return $this->dataResponse(
            MealRecommendationResource::collection($action->run((int) $limit)),
            'Meal recommendations retrieved successfully'
        );
    }
}
