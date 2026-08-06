<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetNewProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealNewProductsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetNewProductsAction $action): JsonResponse
    {
        return $this->dataResponse(
            MealListResource::collection($action->run()),
            'New products retrieved successfully'
        );
    }
}
