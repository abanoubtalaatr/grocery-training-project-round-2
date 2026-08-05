<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Subcategory\ListSubcategoriesAction;
use App\Action\Subcategory\ShowSubcategoryAction;
use App\Action\Subcategory\ListSubcategoryMealsAction;
use App\Action\Subcategory\SubcategoryPresenter;
use App\Http\Requests\Api\ListSubcategoryMealsRequest;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;

class SubcategoryController extends Controller
{
    public function index(ListSubcategoriesAction $action): JsonResponse
    {
        $subcategories = $action->handle(request()->input('category_id'));

        return response()->json(['success' => true, 'message' => 'Subcategories retrieved successfully', 'data' => $subcategories]);
    }

    public function show(string $id, ShowSubcategoryAction $action, SubcategoryPresenter $presenter): JsonResponse
    {
        try {
            $subcategory = $action->handle($id);

            return response()->json(['success' => true, 'message' => 'Subcategory retrieved successfully', 'data' => $presenter->presentShow($subcategory)]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Subcategory not found'], 404);
        }
    }

    public function meals(string $id, ListSubcategoryMealsRequest $request, ListSubcategoryMealsAction $action, SubcategoryPresenter $presenter): JsonResponse
    {
        try {
            $subcategory = Subcategory::findOrFail($id);

            $paginator = $action->handle($subcategory, $request->validated());

            $data = $presenter->presentMealsPaging($subcategory, $paginator);

            $total = $paginator->total();

            return response()->json(array_merge([
                'success' => true,
                'message' => $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
                'data' => $data,
            ], $total === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.'] : []));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Subcategory not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve meals', 'error' => $e->getMessage()], 500);
        }
    }
}
