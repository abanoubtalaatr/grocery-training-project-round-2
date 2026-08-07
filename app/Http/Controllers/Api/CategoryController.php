<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Category\CategoryAction;
use App\Actions\Api\Category\GetCategoryMealAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\CategoryWithMealsResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CategoryAction $categoryAction,
        protected GetCategoryMealAction $getCategoryMealAction,
    ) {}

    /**
     * Get all categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryAction->index();

        return $this->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Get a single category with its meals.
     */
    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        $category = $this->categoryAction->show($category);

        return $this->success(
            new CategoryWithMealsResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Create a category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryAction->store($request);

        return $this->success(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category = $this->categoryAction->update($request, $category);

        return $this->success(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categoryAction->delete($category);

        return $this->success(null, 'Category deleted successfully');
    }

    /**
     * Get meals by category.
     */
    public function meals(Category $category, Request $request): JsonResponse
    {
        $payload = $this->getCategoryMealAction->execute($category, $request);

        return $this->success(
            [
                'category' => $payload['category'],
                'meals' => $payload['meals'],
                'pagination' => $payload['pagination'],
            ],
            $payload['total'] === 0 ? 'No products match your filters.' : 'Meals retrieved successfully'
        );
    }
}
