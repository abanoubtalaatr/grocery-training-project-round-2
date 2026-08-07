<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardIndexRequest;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Models\Meal;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard statistics and insights.
     */
    public function index(DashboardIndexRequest $request): JsonResponse
    {
        $data = $this->dashboardService->getDashboardData(
            user: $request->user(),
            recentLimit: $request->validated('recent_limit', 5),
            topPurchasesLimit: $request->validated('top_purchases_limit', 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Create a new meal.
     */
    public function storeMeal(StoreMealRequest $request): JsonResponse
    {
        $meal = $this->dashboardService->createMeal($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Meal created successfully',
            'data' => $meal,
        ], 201);
    }

    /**
     * Update an existing meal.
     */
    public function updateMeal(UpdateMealRequest $request, Meal $meal): JsonResponse
    {
        $updatedMeal = $this->dashboardService->updateMeal($meal, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Meal updated successfully',
            'data' => $updatedMeal,
        ]);
    }

    /**
     * Delete a meal.
     */
    public function destroyMeal(Meal $meal): JsonResponse
    {
        $this->dashboardService->deleteMeal($meal);

        return response()->json([
            'success' => true,
            'message' => 'Meal deleted successfully',
        ]);
    }
}