<?php

namespace App\Action\Api;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class GetSubcategoryMealsAction
{
    public function execute(Subcategory $subcategory, array $filters = [])
    {
        $query = $subcategory->meals()->with('category')->available();

        if (array_key_exists('featured', $filters) && $filters['featured'] !== null) {
            $filters['featured'] ? $query->featured() : $query->where('is_featured', false);
        }

        if (array_key_exists('in_stock', $filters) && $filters['in_stock'] !== null) {
            $filters['in_stock'] ? $query->inStock() : $query->outOfStock();
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

        $perPage = isset($filters['per_page']) ? min(max((int) $filters['per_page'], 1), 50) : 15;

        return $query->paginate($perPage);
    }
}
