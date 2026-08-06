<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Api\GetCategoriesAction;
use App\Action\Api\ShowCategoryAction;
use App\Action\Api\GetCategoryMealsAction;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\CategoryWithMealsResource;
use App\Http\Resources\Api\MealResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all categories
     */
    public function index(GetCategoriesAction $action): JsonResponse
    {
        $categories = $action->execute();

        return $this->success(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    /**
     * Get single category with meals
     */
    public function show(Category $category, ShowCategoryAction $action): JsonResponse
    {
        $category = $action->execute($category);

        return $this->success(new CategoryWithMealsResource($category), 'Category retrieved successfully');
    }

    /**
     * Get meals by category (paginated)
     */
    public function meals(Category $category, Request $request, GetCategoryMealsAction $action): JsonResponse
    {
        $filters = [
            'featured' => $request->has('featured') ? $request->boolean('featured') : null,
            'subcategory_id' => $request->input('subcategory_id'),
            'in_stock' => $request->has('in_stock') ? $request->boolean('in_stock') : null,
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'per_page' => $request->input('per_page', 15),
        ];

        $paginator = $action->execute($category, $filters);

        // Transform each meal using MealResource and keep pagination meta
        $paginator = $paginator->through(function ($meal) use ($request) {
            return (new MealResource($meal))->toArray($request);
        });

        $total = $paginator->total();

        return $this->success([
            'category' => new CategoryResource($category),
            'meals' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $total,
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully');
    }
}
