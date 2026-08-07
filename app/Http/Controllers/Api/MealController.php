<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealFilterRequest;
use App\Http\Resources\MealDetailResource;
use App\Http\Resources\MealResource;
use App\Models\Meal;
use App\Services\FrequencyService;
use App\Services\MealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function __construct(
        protected MealService $mealService
    ) {}

    /**
     * Get meals the authenticated user orders most often.
     */
    public function frequency(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to view frequency meals.',
            ], 401);
        }

        $frequencyType = $request->input('frequency_type', FrequencyService::FREQUENCY_WEEKLY);
        $subcategoryId = $request->filled('subcategory_id') ? (int) $request->input('subcategory_id') : null;

        $meals = $this->mealService->getFrequencyMeals($user, $frequencyType, $subcategoryId);

        $payload = [
            'success' => true,
            'message' => 'Frequency meals retrieved successfully',
            'frequency_type' => $frequencyType,
            'data' => MealResource::collection($meals),
        ];

        if ($subcategoryId !== null) {
            $payload['subcategory_id'] = $subcategoryId;
        }

        return response()->json($payload);
    }

    /**
     * Get all available meals (supports filter & search).
     */
    public function index(MealFilterRequest $request): JsonResponse
    {
        $user = $request->user();
        $meals = $this->mealService->getFilteredMeals($request->validated());

        if ($user) {
            $favoriteIds = $user->favorites()->pluck('meal_id')->toArray();
            $request->attributes->set('favorite_meal_ids', $favoriteIds);
        }

        $isEmpty = $meals->isEmpty();

        return response()->json(array_merge([
            'success' => true,
            'message' => $isEmpty ? 'No products match your filters.' : 'Meals retrieved successfully',
            'data' => MealResource::collection($meals),
            'total_count' => $meals->count(),
            'filters_applied' => $request->validated(),
        ], $isEmpty ? ['empty_message' => 'No products match the applied filters. Try adjusting your search or filters.'] : []));
    }

    /**
     * Get single meal details.
     */
    public function show(string $id): JsonResponse
    {
        $meal = $this->mealService->getMealWithDetails($id);

        return response()->json([
            'success' => true,
            'message' => 'Meal retrieved successfully',
            'data' => new MealDetailResource($meal),
        ]);
    }

    /**
     * Get recommended meals.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $meals = $this->mealService->getRecommendations($limit);

        $data = MealResource::collection($meals)->map(function ($resource) {
            $array = $resource->resolve();
            $array['recommendation_reason'] = $resource->resource->recommendation_reason ?? null;
            return $array;
        });

        return response()->json([
            'success' => true,
            'message' => 'Meal recommendations retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get hot / Ready-to-eat meals only.
     */
    public function hot(): JsonResponse
    {
        $meals = Meal::with('category')->available()->hot()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Hot meals retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    /**
     * Get today's deals.
     */
    public function today(): JsonResponse
    {
        $meals = Meal::with('category')->available()->withActiveDiscount()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Today\'s deals retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    public function moreToExplore(): JsonResponse
    {
        $meals = Meal::with('category')->available()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'More to explore retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    public function slider(): JsonResponse
    {
        $meals = Meal::with('category')->available()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Today\'s meals retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    public function bestSells(): JsonResponse
    {
        $meals = Meal::with('category')->available()->take(10)->get();

        return response()->json([
            'success' => true,
            'message' => 'Best sells retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    public function newProducts(): JsonResponse
    {
        $meals = Meal::with('category')->available()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'New products retrieved successfully',
            'data' => MealResource::collection($meals),
        ]);
    }

    public function brands(): JsonResponse
    {
        $brands = Meal::distinct()->pluck('brand')->filter()->values();

        return response()->json([
            'success' => true,
            'message' => 'Brands retrieved successfully',
            'data' => $brands,
        ]);
    }
}