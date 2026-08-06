<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetFrequencyMealsAction;
use App\Action\Api\ListMealsAction;
use App\Action\Api\ShowMealAction;
use App\Action\Api\GetRecommendationsAction;
use App\Action\Api\GetHotMealsAction;
use App\Action\Api\GetTodayDealsAction;
use App\Action\Api\GetMoreToExploreAction;
use App\Action\Api\GetBrandsAction;
use App\Action\Api\GetBestSellsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealResource;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    use ApiResponse;

    public function frequency(Request $request, GetFrequencyMealsAction $action): JsonResponse
    {
        $frequencyType = $request->input('frequency_type', \App\Services\FrequencyService::FREQUENCY_WEEKLY);
        if (! in_array($frequencyType, \App\Services\FrequencyService::VALID_TYPES, true)) {
            $frequencyType = \App\Services\FrequencyService::FREQUENCY_WEEKLY;
        }

        $user = $request->user();
        if ($user === null) {
            return $this->error('Authentication required to view frequency meals.', 401);
        }

        $subcategoryId = $request->input('subcategory_id');
        $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

        $meals = $action->execute($user, $frequencyType, 50, $subcategoryId);

        return $this->success(MealResource::collection($meals), 'Frequency meals retrieved successfully');
    }

    public function moreToExplore(GetMoreToExploreAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), "More to explore retrieved successfully");
    }

    public function brands(GetBrandsAction $action): JsonResponse
    {
        $brands = $action->execute();

        return $this->success($brands, 'Brands retrieved successfully');
    }

    public function slider(GetMoreToExploreAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), "Today's meals retrieved successfully");
    }

    public function bestSells(GetBestSellsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), 'Best sells retrieved successfully');
    }

    public function newProducts(GetMoreToExploreAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), 'New products retrieved successfully');
    }

    public function hot(GetHotMealsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), 'Hot meals retrieved successfully');
    }

    public function today(GetTodayDealsAction $action): JsonResponse
    {
        $meals = $action->execute();

        return $this->success(MealResource::collection($meals), "Today's deals retrieved successfully");
    }

    public function index(Request $request, ListMealsAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($request, $user);

        $meals = $result['meals'];
        $favoriteMealIds = $result['favoriteMealIds'] ?? [];

        $data = $meals->map(function ($meal) use ($favoriteMealIds, $request) {
            $array = (new MealResource($meal))->toArray($request);
            $array['is_favorited'] = in_array($meal->id, $favoriteMealIds);
            return $array;
        });

        $totalCount = $data->count();

        return $this->success(array_merge([
            'data' => $data,
            'total_count' => $totalCount,
            'filters_applied' => [
                'search' => $request->input('search'),
                'category_id' => $request->input('category_id'),
                'subcategory_id' => $request->input('subcategory_id'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'min_rating' => $request->input('min_rating'),
                'brand' => $request->input('brand'),
                'featured' => $request->boolean('featured'),
                'in_stock' => $request->boolean('in_stock'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ],
        ], $totalCount === 0 ? ['empty_message' => 'No products match the applied filters. Try adjusting your search or filters.'] : []), 'Meals retrieved successfully');
    }

    public function recommendations(Request $request, GetRecommendationsAction $action): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $recommendations = $action->execute($limit);

        $meals = $recommendations->map(function ($meal) use ($action, $request) {
            $array = (new MealResource($meal))->toArray($request);
            $array['recommendation_reason'] = $action->getRecommendationReason($meal);
            return $array;
        });

        return $this->success($meals->values(), 'Meal recommendations retrieved successfully');
    }

    public function show(Meal $meal, ShowMealAction $action): JsonResponse
    {
        $meal = $action->execute($meal);

        return $this->success(new MealResource($meal), 'Meal retrieved successfully');
    }
}
