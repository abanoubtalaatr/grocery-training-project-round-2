<?php

namespace App\Http\Controllers\Api;

use App\Actions\Category\GetCategoriesAction;
use App\Actions\Category\GetCategoryAction;
use App\Actions\Category\GetCategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetCategoryMealsRequest;
use App\Http\Resources\CategoryDetailsResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected GetCategoriesAction $getCategoriesAction,
        protected GetCategoryAction $getCategoryAction,
        protected GetCategoryMealsAction $getCategoryMealsAction,
    ) {}

    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => CategoryResource::collection(
                    $this->getCategoriesAction->execute()
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single category
     */
    public function show(string $id): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => new CategoryDetailsResource(
                    $this->getCategoryAction->execute($id)
                ),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get meals by category
     */
    public function meals(GetCategoryMealsRequest $request, string $id): JsonResponse
    {
        try {
            $result = $this->getCategoryMealsAction->execute(
                $id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Meals retrieved successfully',
                'data' => $result,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}