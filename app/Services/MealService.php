<?php

namespace App\Services;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MealService
{
    public function __construct(
        protected FrequencyService $frequencyService
    ) {}

    /**
     * Get user's frequently ordered meals.
     */
    public function getFrequencyMeals(User $user, string $frequencyType, ?int $subcategoryId): Collection
    {
        $validType = in_array($frequencyType, FrequencyService::VALID_TYPES, true) 
            ? $frequencyType 
            : FrequencyService::FREQUENCY_WEEKLY;

        return $this->frequencyService->getFrequentlyOrderedMeals($user, $validType, 50, $subcategoryId);
    }

    /**
     * Get filtered and sorted list of meals.
     */
    public function getFilteredMeals(array $filters): Collection
    {
        $query = Meal::with(['category', 'subcategory'])->available();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }

        if (isset($filters['featured'])) {
            filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if (isset($filters['in_stock'])) {
            filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)
                ? $query->inStock()
                : $query->outOfStock();
        }

        if (isset($filters['min_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$filters['min_price']]);
        }

        if (isset($filters['max_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$filters['max_price']]);
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowedSortFields = ['created_at', 'price', 'rating', 'title', 'sold_count'];
        if (in_array($sortBy, $allowedSortFields, true)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->get();
    }

    /**
     * Get recommendation list for user.
     */
    public function getRecommendations(int $limit = 10): Collection
    {
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

        return $featuredMeals->merge($randomMeals)
            ->shuffle()
            ->take($limit)
            ->map(function ($meal) {
                $meal->recommendation_reason = $this->getRecommendationReason($meal);
                return $meal;
            });
    }

    /**
     * Determine recommendation reason logic.
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

    /**
     * Get single meal with loaded relations.
     */
    public function getMealWithDetails(string $id): Meal
    {
        return Meal::with([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()
                ->with('user:id,username,firstname,lastname')
                ->orderBy('created_at', 'desc'),
        ])->findOrFail($id);
    }
}
