<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetSubcategoriesAction;
use App\Action\Api\ShowSubcategoryAction;
use App\Action\Api\GetSubcategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealResource;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Subcategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetSubcategoriesAction $action): JsonResponse
    {
        $subcategories = $action->execute($request);

        $data = $subcategories->map(function ($subcategory) {
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
                'description' => $subcategory->description,
                'image_url' => $subcategory->image_url,
                'order' => $subcategory->order,
                'category' => [
                    'id' => $subcategory->category->id,
                    'name' => $subcategory->category->name,
                    'slug' => $subcategory->category->slug,
                ],
                'meals_count' => $subcategory->meals()->available()->count(),
                'created_at' => $subcategory->created_at,
            ];
        });

        return $this->success($data, 'Subcategories retrieved successfully');
    }

    public function show(Subcategory $subcategory, ShowSubcategoryAction $action): JsonResponse
    {
        $subcategory = $action->execute($subcategory);

        $meals = $subcategory->meals->map(function ($meal) {
            return (new MealResource($meal))->toArray(request());
        });

        return $this->success([
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'slug' => $subcategory->slug,
            'description' => $subcategory->description,
            'image_url' => $subcategory->image_url,
            'order' => $subcategory->order,
            'is_active' => $subcategory->is_active,
            'category' => [
                'id' => $subcategory->category->id,
                'name' => $subcategory->category->name,
                'slug' => $subcategory->category->slug,
            ],
            'meals' => $meals,
            'meals_count' => $subcategory->meals()->available()->count(),
            'created_at' => $subcategory->created_at,
            'updated_at' => $subcategory->updated_at,
        ], 'Subcategory retrieved successfully');
    }

    public function meals(Subcategory $subcategory, Request $request, GetSubcategoryMealsAction $action): JsonResponse
    {
        $filters = [
            'featured' => $request->has('featured') ? $request->boolean('featured') : null,
            'in_stock' => $request->has('in_stock') ? $request->boolean('in_stock') : null,
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'per_page' => $request->input('per_page', 15),
        ];

        $paginator = $action->execute($subcategory, $filters);

        $meals = $paginator->getCollection()->map(function ($meal) {
            return (new MealResource($meal))->toArray(request());
        });

        $total = $paginator->total();

        return $this->success([
            'subcategory' => [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
            ],
            'meals' => $meals->values()->all(),
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
