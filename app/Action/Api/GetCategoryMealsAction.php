<?php

namespace App\Action\Api;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetCategoryMealsAction
{
    /**
     * Return paginated meals for a given category applying filters, sorting and eager loads
     *
     * @param Category $category
     * @param array $filters (featured, subcategory_id, in_stock, sort_by, sort_order, per_page)
     */
    public function execute(Category $category, array $filters = []): LengthAwarePaginator
    {
        $query = $category->meals()->with(['subcategory'])->available();

        if (array_key_exists('featured', $filters)) {
            $filters['featured'] ? $query->featured() : $query->where('is_featured', false);
        }

        if (!empty($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }

        if (array_key_exists('in_stock', $filters)) {
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
