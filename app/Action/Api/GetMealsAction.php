<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetMealsAction
{
    public function execute(array $filters, $user = null): array
    {
        $query = Meal::with(['category', 'subcategory'])->available();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
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
            $filters['featured'] ? $query->featured() : $query->where('is_featured', false);
        }

        if (isset($filters['in_stock'])) {
            $filters['in_stock'] ? $query->inStock() : $query->outOfStock();
        }

        if (!empty($filters['min_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$filters['min_price']]);
        }

        if (!empty($filters['max_price'])) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$filters['max_price']]);
        }

        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

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

        $meals = $query->get();

        $favoriteMealIds = [];
        if ($user) {
            $favoriteMealIds = $user->favorites()->pluck('meal_id')->toArray();
        }

        $meals->each(function ($meal) use ($favoriteMealIds) {
            $meal->is_favorited = in_array($meal->id, $favoriteMealIds);
        });

        return [
            'meals' => $meals,
            'total_count' => $meals->count(),
            'filters' => $filters,
        ];
    }
}