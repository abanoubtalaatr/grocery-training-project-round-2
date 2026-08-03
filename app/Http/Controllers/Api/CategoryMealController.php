<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\GetCategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryMealsRequest;
use App\Http\Resources\Api\CategoryMealResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryMealController extends Controller
{
    use ApiResponse;

    /**
     * Get meals by category (paginated)
     */
    public function index(CategoryMealsRequest $request, Category $category, GetCategoryMealsAction $action): JsonResponse
    {
        $meals = $action->handle(
            $category,
            $request->toDto()
        );

        return $this->success(
            CategoryMealResource::collection($meals),
            'Meals retrieved successfully'
        );
    }
}
