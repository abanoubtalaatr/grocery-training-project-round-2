<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MealResource;
use App\Models\Meal;
use App\Services\FrequencyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealController extends Controller
{
    use ApiResponse;

    /**
     * Get meals the authenticated user orders most often (personalized by frequency type).
     */
    public function frequency(Request $request, FrequencyService $service): JsonResponse
    {
        $frequencyType = $request->input('frequency_type', FrequencyService::FREQUENCY_WEEKLY);
        if (! in_array($frequencyType, FrequencyService::VALID_TYPES, true)) {
            $frequencyType = FrequencyService::FREQUENCY_WEEKLY;
        }

        $user = $request->user();
        if ($user === null) {
            return $this->error('Authentication required to view frequency meals.', 401);
        }

        $subcategoryId = $request->input('subcategory_id');
        $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

        $meals = $service->getFrequentlyOrderedMeals($user, $frequencyType, 50, $subcategoryId);

        $data = $meals->map(function ($meal) {
            return [
                'id'             => $meal->id,
                'title'          => $meal->title,
                'slug'           => $meal->slug,
                'description'    => $meal->description,
                'image_url'      => $meal->image_url,
                'offer_title'    => $meal->offer_title,
                ...$meal->getApiPriceAttributes(),
                'has_offer'      => $meal->hasOffer(),
                'category'       => $meal->category ? [
                    'id'   => $meal->category->id,
                    'name' => $meal->category->name,
                ] : null,
                'subcategory'    => $meal->subcategory ? [
                    'id'   => $meal->subcategory->id,
                    'name' => $meal->subcategory->name,
                ] : null,
                'features'       => $meal->features,
                'available_date' => $meal->available_date,
                'created_at'     => $meal->created_at,
                'order_count'    => (int) $meal->getAttribute('order_count'),
            ];
        })->values();

        $payload = [
            'success'        => true,
            'message'        => 'Frequency meals retrieved successfully',
            'frequency_type' => $frequencyType,
            'data'           => $data,
        ];
        if ($subcategoryId !== null) {
            $payload['subcategory_id'] = $subcategoryId;
        }

        return response()->json($payload);
    }

    public function moreToExplore(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MealResource::collection($meals), 'More to explore retrieved successfully');
    }

    public function brands(Request $request): JsonResponse
    {
        $brands = Meal::distinct()->pluck('brand');

        return $this->success($brands, 'Brands retrieved successfully');
    }

    public function slider(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MealResource::collection($meals), "Today's meals retrieved successfully");
    }

    public function bestSells(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->take(10)
            ->get();

        return $this->success(MealResource::collection($meals), 'Best sells retrieved successfully');
    }

    public function newProducts(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MealResource::collection($meals), 'New products retrieved successfully');
    }

    /**
     * Get hot / Ready-to-eat meals only.
     */
    public function hot(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->hot()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MealResource::collection($meals), 'Hot meals retrieved successfully');
    }

    /**
     * Get today's deals (meals with active discounts)
     */
    public function today(Request $request): JsonResponse
    {
        $meals = Meal::with('category')
            ->available()
            ->withActiveDiscount()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MealResource::collection($meals), "Today's deals retrieved successfully");
    }

    /**
     * Get all meals
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Meal::with(['category', 'subcategory'])->available();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        if ($request->has('featured')) {
            $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock') ? $query->inStock() : $query->outOfStock();
        }

        if ($request->has('min_price')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->input('min_price')]);
        }
        if ($request->has('max_price')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->input('max_price')]);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->input('min_rating'));
        }

        if ($request->has('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }
        $allowedSortFields = ['created_at', 'price', 'rating', 'title', 'sold_count'];
        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $favoriteMealIds = $user ? $user->favorites()->pluck('meal_id')->toArray() : [];

        $meals = $query->get()
            ->map(function ($meal) use ($favoriteMealIds) {
                return [
                    'id'             => $meal->id,
                    'title'          => $meal->title,
                    'slug'           => $meal->slug,
                    'description'    => $meal->description,
                    'image_url'      => $meal->image_url,
                    'offer_title'    => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer'      => $meal->hasOffer(),
                    'rating'         => (float) $meal->rating,
                    'rating_count'   => (int) $meal->rating_count,
                    'size'           => $meal->size,
                    'brand'          => $meal->brand,
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock'       => $meal->isInStock(),
                    'is_featured'    => $meal->is_featured,
                    'sold_count'     => $meal->sold_count,
                    'category'       => [
                        'id'   => $meal->category->id,
                        'name' => $meal->category->name,
                    ],
                    'subcategory'    => $meal->subcategory ? [
                        'id'   => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                    ] : null,
                    'features'       => $meal->features,
                    'is_favorited'   => in_array($meal->id, $favoriteMealIds),
                    'created_at'     => $meal->created_at,
                ];
            });

        $totalCount = $meals->count();
        $isEmpty = $totalCount === 0;

        return response()->json(array_merge([
            'success'         => true,
            'message'         => $isEmpty ? 'No products match your filters.' : 'Meals retrieved successfully',
            'data'            => $meals,
            'total_count'     => $totalCount,
            'filters_applied' => [
                'search'         => $request->input('search'),
                'category_id'    => $request->input('category_id'),
                'subcategory_id' => $request->input('subcategory_id'),
                'min_price'      => $request->input('min_price'),
                'max_price'      => $request->input('max_price'),
                'min_rating'     => $request->input('min_rating'),
                'brand'          => $request->input('brand'),
                'featured'       => $request->boolean('featured'),
                'in_stock'       => $request->boolean('in_stock'),
                'sort_by'        => $sortBy,
                'sort_order'     => $sortOrder,
            ],
        ], $isEmpty ? ['empty_message' => 'No products match the applied filters. Try adjusting your search or filters.'] : []));
    }

    /**
     * Get recommended meals
     */
    public function recommendations(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);

        $featuredMeals = Meal::with('category')
            ->available()
            ->featured()
            ->whereNotNull('discount_price')
            ->inRandomOrder()
            ->limit((int) ceil($limit / 2))
            ->get();

        $randomMeals = Meal::with('category')
            ->available()
            ->whereNotIn('id', $featuredMeals->pluck('id'))
            ->inRandomOrder()
            ->limit($limit - $featuredMeals->count())
            ->get();

        $recommendations = $featuredMeals->merge($randomMeals)->shuffle()->take($limit);

        $meals = $recommendations->map(function ($meal) {
            return [
                'id'                   => $meal->id,
                'title'                => $meal->title,
                'slug'                 => $meal->slug,
                'description'          => $meal->description,
                'image_url'            => $meal->image_url,
                'offer_title'          => $meal->offer_title,
                ...$meal->getApiPriceAttributes(),
                'has_offer'            => $meal->hasOffer(),
                'is_featured'          => $meal->is_featured,
                'category'             => [
                    'id'   => $meal->category->id,
                    'name' => $meal->category->name,
                    'slug' => $meal->category->slug,
                ],
                'features'             => $meal->features,
                'recommendation_reason' => $this->getRecommendationReason($meal),
            ];
        });

        return $this->success($meals->values(), 'Meal recommendations retrieved successfully');
    }

    private function getRecommendationReason($meal): string
    {
        if ($meal->is_featured && $meal->discount_price) {
            return 'Featured with special offer';
        }
        if ($meal->is_featured) {
            return 'Featured meal';
        }
        if ($meal->discount_price) {
            return 'Special offer';
        }

        return 'Popular choice';
    }

    /**
     * Get single meal
     */
    public function show(Meal $meal): JsonResponse
    {
        $meal->load([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ]);

        return $this->success([
            'id'                => $meal->id,
            'title'             => $meal->title,
            'slug'              => $meal->slug,
            'description'       => $meal->description,
            'image_url'         => $meal->image_url,
            'offer_title'       => $meal->offer_title,
            ...$meal->getApiPriceAttributes(),
            'has_offer'         => $meal->hasOffer(),
            'rating'            => (float) $meal->rating,
            'rating_count'      => (int) $meal->rating_count,
            'size'              => $meal->size,
            'brand'             => $meal->brand,
            'includes'          => $meal->includes,
            'how_to_use'        => $meal->how_to_use,
            'features'          => $meal->features,
            'expiry_date'       => $meal->expiry_date,
            'days_until_expiry' => $meal->daysUntilExpiry(),
            'is_expired'        => $meal->isExpired(),
            'stock_quantity'    => $meal->stock_quantity,
            'in_stock'          => $meal->isInStock(),
            'sold_count'        => $meal->sold_count,
            'is_featured'       => $meal->is_featured,
            'is_available'      => $meal->is_available,
            'available_date'    => $meal->available_date,
            'category'          => [
                'id'   => $meal->category->id,
                'name' => $meal->category->name,
                'slug' => $meal->category->slug,
            ],
            'reviews'           => $meal->reviews->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'user'       => $review->relationLoaded('user') && $review->user ? [
                        'id'   => $review->user->id,
                        'name' => $review->user->full_name ?? $review->user->username ?? 'User',
                    ] : null,
                    'rating'     => (int) $review->rating,
                    'comment'    => $review->comment,
                    'images'     => $review->images ?? [],
                    'created_at' => $review->created_at?->toIso8601String(),
                ];
            })->values(),
            'subcategory'       => $meal->subcategory ? [
                'id'   => $meal->subcategory->id,
                'name' => $meal->subcategory->name,
                'slug' => $meal->subcategory->slug,
            ] : null,
            'created_at'        => $meal->created_at,
            'updated_at'        => $meal->updated_at,
        ], 'Meal retrieved successfully');
    }
}
