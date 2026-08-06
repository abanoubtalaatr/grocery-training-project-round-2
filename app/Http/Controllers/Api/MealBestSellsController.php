<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetBestSellsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealBestSellsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetBestSellsAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'Best sells retrieved successfully'
        );
    }
}
