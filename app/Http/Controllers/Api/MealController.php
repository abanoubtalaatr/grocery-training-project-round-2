<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\IndexMealAction;
use App\Actions\Api\Meal\ShowMealAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexMealRequest;
use App\Http\Resources\Api\MealDetailResource;
use App\Http\Resources\Api\MealListResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class MealController extends Controller
{
    use ApiTrait;

    /**
     * Get all meals
     */
    public function index(IndexMealRequest $request, IndexMealAction $action): JsonResponse
    {
            $user = $request->user();
            $filters = $request->validated();
            
            $meals = $action->run($filters, $user);
            
            $totalCount = $meals->count();
            $isEmpty = $totalCount === 0;

            return response()->json(array_merge([
                'success' => true,
                'message' => $isEmpty ? 'No products match your filters.' : 'Meals retrieved successfully',
                'data' => MealListResource::collection($meals),
                'total_count' => $totalCount,
                'filters_applied' => $filters,
            ], $isEmpty ? ['empty_message' => 'No products match the applied filters. Try adjusting your search or filters.'] : []));

    }

    /**
     * Get single meal
     */
    public function show(string $id, ShowMealAction $action): JsonResponse
    {
            $meal = $action->run($id);

            return $this->dataResponse(
                new MealDetailResource($meal),
                'Meal retrieved successfully'
            );

    }
}
