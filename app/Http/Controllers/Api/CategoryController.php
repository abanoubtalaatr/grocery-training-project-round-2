<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Category\GetAllCategoriesAction;
use App\Actions\Api\Category\ShowCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryDetailsResource;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiTrait;

    /**
     * Display all categories.
     */
    public function index(GetAllCategoriesAction $action): JsonResponse
    {
        $categories = $action->run();

        return $this->dataResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Display category with meals & subcategories.
     */
    public function show(Category $category, ShowCategoryAction $action): JsonResponse
    {
        $category = $action->run($category);

        return $this->dataResponse(
            new CategoryDetailsResource($category),
            'Category retrieved successfully'
        );
    }
}