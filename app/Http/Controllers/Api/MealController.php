<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Meal\TodayMealsAction;
use App\Action\Meal\HotMealsAction;
use App\Action\Meal\RecommendationsAction;
use App\Action\Meal\FrequencyMealsAction;
use App\Action\Meal\SliderAction;
use App\Action\Meal\BestSellsAction;
use App\Action\Meal\NewProductsAction;
use App\Action\Meal\BrandsAction;
use App\Action\Meal\MoreToExploreAction;
use App\Action\Meal\ListMealsAction;
use App\Action\Meal\ShowMealAction;
use App\Action\Meal\MealPresenter;
use App\Http\Requests\Api\ListMealsRequest;
use App\Http\Requests\Api\FrequencyMealsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('cache.ttl_minutes', 1) * 60 ?: 60; // fallback to 60s
    }

    public function frequency(FrequencyMealsRequest $request, FrequencyMealsAction $action, MealPresenter $presenter): JsonResponse
    {
        $frequencyType = $request->input('frequency_type');
        $frequencyType = $frequencyType ?: \App\Services\FrequencyService::FREQUENCY_WEEKLY;
        if (! in_array($frequencyType, \App\Services\FrequencyService::VALID_TYPES, true)) {
            $frequencyType = \App\Services\FrequencyService::FREQUENCY_WEEKLY;
        }

        $user = $request->user();
        if ($user === null) {
            return response()->json(['success' => false, 'message' => 'Authentication required to view frequency meals.'], 401);
        }

        $subcategoryId = $request->input('subcategory_id');
        $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

        $meals = $action->handle($user, $frequencyType, $subcategoryId, 50);

        $data = $meals->map(fn($meal) => $presenter->presentListItem($meal, ['order_count' => (int) $meal->getAttribute('order_count')]))->values();

        $payload = ['success' => true, 'message' => 'Frequency meals retrieved successfully', 'frequency_type' => $frequencyType, 'data' => $data];
        if ($subcategoryId !== null) $payload['subcategory_id'] = $subcategoryId;

        return response()->json($payload);
    }

    public function today(Request $request, TodayMealsAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 0);
        $cacheKey = 'meals:today:' . md5((string) $limit);

        $meals = cache()->remember($cacheKey, 60, function () use ($action, $limit) {
            return $action->handle(['limit' => $limit])->map->toArray();
        });

        // Ensure presenter format
        $formatted = collect($meals)->map(fn($m) => $m instanceof \App\Models\Meal ? $presenter->presentSmall($m) : $m)->values();

        return response()->json(['success' => true, 'message' => "Today's deals retrieved successfully", 'data' => $formatted]);
    }

    public function hot(Request $request, HotMealsAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 0);
        $cacheKey = 'meals:hot:' . md5((string) $limit);

        $meals = cache()->remember($cacheKey, 60, function () use ($action, $limit) {
            return $action->handle(['limit' => $limit])->map->toArray();
        });

        $formatted = collect($meals)->map(fn($m) => $m instanceof \App\Models\Meal ? $presenter->presentSmall($m) : $m)->values();

        return response()->json(['success' => true, 'message' => 'Hot meals retrieved successfully', 'data' => $formatted]);
    }

    public function recommendations(Request $request, RecommendationsAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $cacheKey = 'meals:recommendations:' . md5((string) $limit);

        $meals = cache()->remember($cacheKey, 60, function () use ($action, $limit) {
            return $action->handle($limit)->map->toArray();
        });

        $formatted = collect($meals)->map(fn($m) => $m instanceof \App\Models\Meal ? $presenter->presentListItem($m) + ['recommendation_reason' => $this->getRecommendationReason($m)] : $m)->values();

        return response()->json(['success' => true, 'message' => 'Meal recommendations retrieved successfully', 'data' => $formatted]);
    }

    private function getRecommendationReason($meal): string
    {
        if ($meal->is_featured && $meal->discount_price) return 'Featured with special offer';
        if ($meal->is_featured) return 'Featured meal';
        if ($meal->discount_price) return 'Special offer';
        return 'Popular choice';
    }

    public function slider(Request $request, SliderAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 0);
        $meals = $action->handle(['limit' => $limit]);
        $formatted = $meals->map(fn($m) => $presenter->presentSmall($m))->values();
        return response()->json(['success' => true, 'message' => "Today's meals retrieved successfully", 'data' => $formatted]);
    }

    public function bestSells(Request $request, BestSellsAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $meals = $action->handle($limit);
        $formatted = $meals->map(fn($m) => $presenter->presentListItem($m))->values();
        return response()->json(['success' => true, 'message' => 'Best sells retrieved successfully', 'data' => $formatted]);
    }

    public function newProducts(Request $request, NewProductsAction $action, MealPresenter $presenter): JsonResponse
    {
        $limit = (int) $request->input('limit', 0);
        $meals = $action->handle(['limit' => $limit]);
        $formatted = $meals->map(fn($m) => $presenter->presentSmall($m))->values();
        return response()->json(['success' => true, 'message' => 'New products retrieved successfully', 'data' => $formatted]);
    }

    public function moreToExplore(Request $request, MoreToExploreAction $action, MealPresenter $presenter): JsonResponse
    {
        $meals = $action->handle();
        $formatted = $meals->map(fn($m) => $presenter->presentSmall($m))->values();
        return response()->json(['success' => true, 'message' => 'More to explore retrieved successfully', 'data' => $formatted]);
    }

    public function brands(Request $request, BrandsAction $action): JsonResponse
    {
        $brands = $action->handle();
        return response()->json(['success' => true, 'message' => 'Brands retrieved successfully', 'data' => $brands]);
    }

    public function index(ListMealsRequest $request, ListMealsAction $action, MealPresenter $presenter): JsonResponse
    {
        try {
            $result = $action->handle($request->validated());

            if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $items = collect($result->items())->map(fn($meal) => is_array($meal) ? $meal : $presenter->presentListItem($meal))->values();

                return response()->json([
                    'success' => true,
                    'message' => $result->total() === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
                    'data' => $items,
                    'pagination' => [
                        'current_page' => $result->currentPage(),
                        'last_page' => $result->lastPage(),
                        'per_page' => $result->perPage(),
                        'total' => $result->total(),
                        'from' => $result->firstItem(),
                        'to' => $result->lastItem(),
                    ],
                ]);
            }

            // Non-paginated collection
            $meals = collect($result)->map(fn($meal) => is_array($meal) ? $meal : $presenter->presentListItem($meal))->values();
            $totalCount = $meals->count();

            return response()->json([
                'success' => true,
                'message' => $totalCount === 0 ? 'No products match your filters.' : 'Meals retrieved successfully',
                'data' => $meals,
                'total_count' => $totalCount,
                'filters_applied' => $request->only(['search','category_id','subcategory_id','min_price','max_price','min_rating','brand','featured','in_stock','sort_by','sort_order']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve meals', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id, ShowMealAction $action, MealPresenter $presenter): JsonResponse
    {
        try {
            $meal = $action->handle($id);
            return response()->json(['success' => true, 'message' => 'Meal retrieved successfully', 'data' => $presenter->presentDetail($meal)]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Meal not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve meal', 'error' => $e->getMessage()], 500);
        }
    }
}
