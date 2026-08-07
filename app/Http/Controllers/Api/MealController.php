<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetBestSellsAction;
use App\Action\Api\GetBrandsAction;
use App\Action\Api\GetFrequentlyOrderedMealsAction;
use App\Action\Api\GetHotMealsAction;
use App\Action\Api\GetMealRecommendationsAction;
use App\Action\Api\GetMealSliderAction;
use App\Action\Api\GetMealsAction;
use App\Action\Api\GetMoreToExploreAction;
use App\Action\Api\GetNewProductsAction;
use App\Action\Api\GetTodayDealsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BrandResource;
use App\Http\Resources\Api\FrequencyMealResource;
use App\Http\Resources\Api\MealDetailResource;
use App\Http\Resources\Api\MealResource;
use App\Http\Resources\Api\RecommendationMealResource;
use App\Http\Resources\Api\SliderMealResource;
use App\Models\Meal;
use App\Services\FrequencyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetMealsAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($request->all(), $user);

        return $this->success(
            [
                'meals' => MealResource::collection($result['meals']),
                'total_count' => $result['total_count'],
                'filters_applied' => $result['filters'],
            ],
            $result['total_count'] === 0 ? 'No products match your filters.' : 'Meals retrieved successfully');
    }

    public function show(string $id): JsonResponse
    {
        $meal = Meal::with([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ])->findOrFail($id);

        return $this->success(new MealDetailResource($meal),'Meal retrieved successfully');
    }

    public function frequency(Request $request, GetFrequentlyOrderedMealsAction $action): JsonResponse
    {
        $frequencyType = $request->input('frequency_type', FrequencyService::FREQUENCY_WEEKLY);

        if (!in_array($frequencyType, FrequencyService::VALID_TYPES, true)) {
            $frequencyType = FrequencyService::FREQUENCY_WEEKLY;
        }

        $subcategoryId = $request->input('subcategory_id');
        $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

        $meals = $action->execute($request->user(), $frequencyType, $subcategoryId);

        $response = [
            'meals' => FrequencyMealResource::collection($meals),
            'frequency_type' => $frequencyType,
        ];

        if ($subcategoryId !== null) {
            $response['subcategory_id'] = $subcategoryId;
        }

        return $this->success($response, 'Frequency meals retrieved successfully');
    }

    public function moreToExplore(Request $request, GetMoreToExploreAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals),'More to explore retrieved successfully');
    }

    public function brands(Request $request, GetBrandsAction $action): JsonResponse
    {
        $brands = $action->execute();

        return $this->success(BrandResource::collection($brands),'Brands retrieved successfully');
    }

    public function slider(Request $request, GetMealSliderAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(SliderMealResource::collection($meals),'Slider meals retrieved successfully');
    }

    public function bestSells(Request $request, GetBestSellsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals),'Best sells retrieved successfully');
    }

    public function newProducts(Request $request, GetNewProductsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals),'New products retrieved successfully');
    }

    public function hot(Request $request, GetHotMealsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success( MealResource::collection($meals),'Hot meals retrieved successfully');
    }

    public function today(Request $request, GetTodayDealsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals),'Today\'s deals retrieved successfully');
    }

    public function recommendations(Request $request, GetMealRecommendationsAction $action): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $meals = $action->execute($limit);

        return $this->success(RecommendationMealResource::collection($meals),'Meal recommendations retrieved successfully');
    }
}
