<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetMealSliderAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealSliderController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetMealSliderAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'Slider meals retrieved successfully'
        );
    }
}
