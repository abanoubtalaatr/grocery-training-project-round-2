<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\MealAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealDetailResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    use ApiResponse;

    public function __construct(protected MealAction $mealAction) {}

    /**
     * Get meals the authenticated user orders most often.
     */
    public function frequency(Request $request): JsonResponse
    {
        $payload = $this->mealAction->frequency($request);

        return $this->successWithPayload(
            $payload['data'],
            [
                'frequency_type' => $payload['frequency_type'],
                'subcategory_id' => $payload['subcategory_id'],
            ],
            'Frequency meals retrieved successfully'
        );
    }

    /**
     * Get meals for the “more to explore” section.
     */
    public function moreToExplore(): JsonResponse
    {
        $meals = $this->mealAction->moreToExplore();

        return $this->success($meals, 'More to explore retrieved successfully');
    }

    /**
     * Get all meal brands.
     */
    public function brands(): JsonResponse
    {
        $brands = $this->mealAction->brands();

        return $this->success($brands, 'Brands retrieved successfully');
    }

    /**
     * Get meals for the slider section.
     */
    public function slider(): JsonResponse
    {
        $meals = $this->mealAction->slider();

        return $this->success($meals, 'Today\'s meals retrieved successfully');
    }

    /**
     * Get best-selling meals.
     */
    public function bestSells(): JsonResponse
    {
        $meals = $this->mealAction->bestSells();

        return $this->success($meals, 'Best sells retrieved successfully');
    }

    /**
     * Get new products.
     */
    public function newProducts(): JsonResponse
    {
        $meals = $this->mealAction->newProducts();

        return $this->success($meals, 'New products retrieved successfully');
    }

    /**
     * Get hot / ready-to-eat meals only.
     */
    public function hot(): JsonResponse
    {
        $meals = $this->mealAction->hot();

        return $this->success($meals, 'Hot meals retrieved successfully');
    }

    /**
     * Get today’s deals.
     */
    public function today(): JsonResponse
    {
        $meals = $this->mealAction->today();

        return $this->success($meals, 'Today\'s deals retrieved successfully');
    }

    /**
     * Get all meals.
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $this->mealAction->index($request);
        $data = $payload['meals'];
        $extraPayload = [
            'total_count' => $payload['total_count'],
            'filters_applied' => $payload['filters_applied'],
        ];

        if ($payload['total_count'] === 0) {
            $extraPayload['empty_message'] = 'No products match the applied filters. Try adjusting your search or filters.';
        }

        return $this->successWithPayload(
            $data,
            $extraPayload,
            $payload['total_count'] === 0 ? 'No products match your filters.' : 'Meals retrieved successfully'
        );
    }

    /**
     * Get recommended meals.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $meals = $this->mealAction->recommendations($request);

        return $this->success($meals->values(), 'Meal recommendations retrieved successfully');
    }

    /**
     * Get a single meal.
     */
    public function show(string $id): JsonResponse
    {
        $meal = $this->mealAction->show($id);
        $this->authorize('view', $meal);

        return $this->success(new MealDetailResource($meal), 'Meal retrieved successfully');

    }
}
