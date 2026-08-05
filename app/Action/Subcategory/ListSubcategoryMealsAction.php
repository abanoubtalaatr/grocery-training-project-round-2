<?php

namespace App\Action\Subcategory;

use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSubcategoryMealsAction
{
    public function handle(Subcategory $subcategory, array $filters): LengthAwarePaginator
    {
        $query = $subcategory->meals()->available()->with('category:id,name,slug')
            ->select(['id', 'title', 'slug', 'description', 'image', 'offer_title', 'price', 'discount_price', 'rating', 'rating_count', 'is_featured', 'stock_quantity', 'expiry_date', 'features', 'created_at']);

        if (isset($filters['featured'])) {
            $filters['featured'] ? $query->featured() : $query->where('is_featured', false);
        }

        if (isset($filters['in_stock'])) {
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

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }
}
