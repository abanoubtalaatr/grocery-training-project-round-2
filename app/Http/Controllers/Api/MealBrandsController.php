<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetMealBrandsAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealBrandsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetMealBrandsAction $action): JsonResponse
    {
        return $this->dataResponse(
            $action->run(),
            'Brands retrieved successfully'
        );
    }
}
