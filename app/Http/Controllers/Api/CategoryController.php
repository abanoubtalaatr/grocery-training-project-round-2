<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Category\ListCategoriesAction;
use App\Action\Category\ShowCategoryAction;
use App\Action\Category\ListCategoryMealsAction;
use App\Action\Category\CategoryPresenter;
use App\Http\Requests\Api\ListCategoryMealsRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(ListCategoriesAction $action): JsonResponse
    {
        $categories = $action->handle();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function show(string $id, ShowCategoryAction $action, CategoryPresenter $presenter): JsonResponse
    {
        try {
            $category = $action->handle($id);

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $presenter->presentCategoryWithMeals($category),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
    }

    public function meals(string $id, ListCategoryMealsRequest $request, ListCategoryMealsAction $action, CategoryPresenter $presenter): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $paginator = $action->handle($category, $request->validated());

            $data = $presenter->presentMealPaging($category, $paginator);

            $total = $paginator->total();

            return response()->json(array_merge([
                'success' => true,
                'message' => $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
                'data' => $data,
            ], $total === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.'] : []));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve meals', 'error' => $e->getMessage()], 500);
        }
    }
}
