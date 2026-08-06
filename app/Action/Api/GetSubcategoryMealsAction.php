<?php

namespace App\Action\Api;

use App\Models\Subcategory;

class GetSubcategoryMealsAction
{
    public function execute(string $id, array $filters): array
    {
        $subcategory = Subcategory::findOrFail($id);

        $query = $subcategory->meals()->with('category')->available();

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
        $paginator = $query->paginate($perPage);

        return [
            'subcategory' => [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
            ],
            'meals' => $paginator->getCollection(),
            'paginator' => $paginator,
        ];
    }
}