<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetSubcategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealResource;
use App\Http\Resources\Api\SubcategoryResource;
use App\Http\Resources\Api\SubcategoryWithMealsResource;
use App\Models\Subcategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Subcategory::with('category')->active();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $subcategories = $query->inRandomOrder()->get();

        return $this->success(SubcategoryResource::collection($subcategories),'Subcategories retrieved successfully');
    }

    public function show(string $id): JsonResponse
    {
        $subcategory = Subcategory::with(['category', 'meals' => function ($query) {
            $query->available()->limit(10);
        }])->findOrFail($id);

        return $this->success(new SubcategoryWithMealsResource($subcategory),'Subcategory retrieved successfully');
    }

    public function meals(string $id, Request $request, GetSubcategoryMealsAction $action): JsonResponse
    {
        $result = $action->execute($id, $request->all());

        $total = $result['paginator']->total();

        return $this->success(
            [
                'subcategory' => [
                    'id' => $result['subcategory']['id'],
                    'name' => $result['subcategory']['name'],
                    'slug' => $result['subcategory']['slug'],
                ],
                'meals' => MealResource::collection($result['meals']),
                'pagination' => [
                    'current_page' => $result['paginator']->currentPage(),
                    'last_page' => $result['paginator']->lastPage(),
                    'per_page' => $result['paginator']->perPage(),
                    'total' => $total,
                    'from' => $result['paginator']->firstItem(),
                    'to' => $result['paginator']->lastItem(),
                ],
            ],
            $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully'
        );
    }
}
