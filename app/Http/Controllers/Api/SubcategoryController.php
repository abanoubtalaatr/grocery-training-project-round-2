<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Subcategory\GetSubcategoryMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealCardResource;
use App\Http\Resources\Api\SubcategoryResource;
use App\Http\Resources\Api\SubcategoryShowResource;
use App\Models\Subcategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all subcategories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subcategory::with('category')->active();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $subcategories = $query->inRandomOrder()->get();

        return $this->success(
            SubcategoryResource::collection($subcategories),
            'Subcategories retrieved successfully'
        );
    }

    /**
     * Get single subcategory
     */
    public function show(string $id): JsonResponse
    {
        $subcategory = Subcategory::with(['category', 'meals' => function ($query) {
            $query->available()->limit(10);
        }])->findOrFail($id);

        return $this->success(
            new SubcategoryShowResource($subcategory),
            'Subcategory retrieved successfully'
        );
    }

    /**
     * Get meals by subcategory (paginated)
     */
    public function meals(string $id, Request $request, GetSubcategoryMealsAction $action): JsonResponse
    {
        $subcategory = Subcategory::findOrFail($id);
        $paginator = $action->execute($subcategory, $request);

        $total = $paginator->total();
        $data = [
            'success' => true,
            'message' => $total === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
            'data' => [
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ],
                'meals' => MealCardResource::collection($paginator->getCollection())->values(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $total,
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ];

        if ($total === 0) {
            $data['empty_message'] = 'No products match the applied filters. Try adjusting your filters.';
        }

        return response()->json($data);
    }
}

