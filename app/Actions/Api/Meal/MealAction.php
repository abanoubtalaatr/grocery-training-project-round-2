<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use App\Services\FrequencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MealAction
{
    public function __construct(protected FrequencyService $frequencyService) {}

    /**
     * Get meals the authenticated user orders most often.
     */
    public function frequency(Request $request): array
    {
        try {
            $frequencyType = $request->input('frequency_type', FrequencyService::FREQUENCY_WEEKLY);
            if (! in_array($frequencyType, FrequencyService::VALID_TYPES, true)) {
                $frequencyType = FrequencyService::FREQUENCY_WEEKLY;
            }

            $user = $request->user();
            if ($user === null) {
                throw new \RuntimeException('Authentication required to view frequency meals.');
            }

            $subcategoryId = $request->input('subcategory_id');
            $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

            $meals = $this->frequencyService->getFrequentlyOrderedMeals($user, $frequencyType, 50, $subcategoryId);

            $data = $meals->map(function (Meal $meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                    'category' => $meal->category ? [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                    ] : null,
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                    ] : null,
                    'features' => $meal->features,
                    'available_date' => $meal->available_date,
                    'created_at' => $meal->created_at,
                    'order_count' => (int) $meal->getAttribute('order_count'),
                ];
            })->values();

            return [
                'data' => $data,
                'frequency_type' => $frequencyType,
                'subcategory_id' => $subcategoryId,
            ];
        } catch (Throwable $e) {
            Log::error('Frequency meals error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get meals for the “more to explore” section.
     */
    public function moreToExplore()
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all meal brands.
     */
    public function brands()
    {
        return Meal::distinct()->pluck('brand');
    }

    /**
     * Get meals for the slider section.
     */
    public function slider()
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Meal $meal): array {
                return $this->formatSimpleMeal($meal);
            });
    }

    /**
     * Get best-selling meals.
     */
    public function bestSells()
    {
        return Meal::with('category')
            ->available()
            ->take(10)
            ->get();
    }

    /**
     * Get new products.
     */
    public function newProducts()
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get hot meals.
     */
    public function hot()
    {
        return Meal::with('category')
            ->available()
            ->hot()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Meal $meal): array {
                return $this->formatSimpleMeal($meal);
            });
    }

    /**
     * Get today’s deals.
     */
    public function today()
    {
        return Meal::with('category')
            ->available()
            ->withActiveDiscount()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Meal $meal): array {
                return $this->formatSimpleMeal($meal);
            });
    }

    /**
     * Get meals with filters and sorting.
     */
    public function index(Request $request): array
    {
        $user = $request->user();
        $query = Meal::with(['category', 'subcategory'])->available();

        if ($request->has('search') && $request->filled('search')) {
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
            $minPrice = $request->input('min_price');
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$minPrice]);
        }

        if ($request->has('max_price')) {
            $maxPrice = $request->input('max_price');
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$maxPrice]);
        }

        if ($request->has('min_rating')) {
            $minRating = $request->input('min_rating');
            $query->where('rating', '>=', $minRating);
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
        if (in_array($sortBy, $allowedSortFields, true)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) '.$sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $favoriteMealIds = [];
        if ($user) {
            $favoriteMealIds = $user->favorites()->pluck('meal_id')->toArray();
        }

        $meals = $query->get()->map(function (Meal $meal) use ($favoriteMealIds): array {
            return [
                'id' => $meal->id,
                'title' => $meal->title,
                'slug' => $meal->slug,
                'description' => $meal->description,
                'image_url' => $meal->image_url,
                'offer_title' => $meal->offer_title,
                ...$meal->getApiPriceAttributes(),
                'has_offer' => $meal->hasOffer(),
                'rating' => (float) $meal->rating,
                'rating_count' => (int) $meal->rating_count,
                'size' => $meal->size,
                'brand' => $meal->brand,
                'stock_quantity' => $meal->stock_quantity,
                'in_stock' => $meal->isInStock(),
                'is_featured' => $meal->is_featured,
                'sold_count' => $meal->sold_count,
                'category' => [
                    'id' => $meal->category->id,
                    'name' => $meal->category->name,
                ],
                'subcategory' => $meal->subcategory ? [
                    'id' => $meal->subcategory->id,
                    'name' => $meal->subcategory->name,
                ] : null,
                'features' => $meal->features,
                'is_favorited' => in_array($meal->id, $favoriteMealIds),
                'created_at' => $meal->created_at,
            ];
        });

        return [
            'meals' => $meals,
            'total_count' => $meals->count(),
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
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ];
    }

    /**
     * Get meal recommendations.
     */
    public function recommendations(Request $request)
    {
        $limit = $request->input('limit', 10);

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

        return $featuredMeals->merge($randomMeals)->shuffle()->take($limit)->map(function (Meal $meal): array {
            return [
                'id' => $meal->id,
                'title' => $meal->title,
                'slug' => $meal->slug,
                'description' => $meal->description,
                'image_url' => $meal->image_url,
                'offer_title' => $meal->offer_title,
                ...$meal->getApiPriceAttributes(),
                'has_offer' => $meal->hasOffer(),
                'is_featured' => $meal->is_featured,
                'category' => [
                    'id' => $meal->category->id,
                    'name' => $meal->category->name,
                    'slug' => $meal->category->slug,
                ],
                'features' => $meal->features,
                'recommendation_reason' => $this->getRecommendationReason($meal),
            ];
        });
    }

    /**
     * Get a single meal.
     */
    public function show(string $id): Meal
    {
        return Meal::with([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ])->findOrFail($id);
    }

    /**
     * Build a simple meal payload for home-page style endpoints.
     */
    private function formatSimpleMeal(Meal $meal): array
    {
        return [
            'id' => $meal->id,
            'title' => $meal->title,
            'slug' => $meal->slug,
            'description' => $meal->description,
            'image_url' => $meal->image_url,
            'offer_title' => $meal->offer_title,
            ...$meal->getApiPriceAttributes(),
            'has_offer' => $meal->hasOffer(),
            'category' => [
                'id' => $meal->category->id,
                'name' => $meal->category->name,
            ],
            'features' => $meal->features,
            'available_date' => $meal->available_date,
            'created_at' => $meal->created_at,
        ];
    }

    /**
     * Get the recommendation reason for a meal.
     */
    private function getRecommendationReason(Meal $meal): string
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
}
