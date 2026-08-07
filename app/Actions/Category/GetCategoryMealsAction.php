<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetCategoryMealsAction
{
    public function execute(string $id, array $filters)
    {
        $category = Category::findOrFail($id);

        $query = $category->meals()
            ->with('subcategory')
            ->available();

        if (isset($filters['featured'])) {
            $filters['featured']
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if (isset($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }

        if (isset($filters['in_stock'])) {
            $filters['in_stock']
                ? $query->inStock()
                : $query->outOfStock();
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowed = ['created_at', 'price', 'rating', 'title', 'sold_count'];

        if (in_array($sortBy, $allowed)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) '.$sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 50);

        return [
            'category' => $category,
            'meals' => $query->paginate($perPage),
        ];
    }
}