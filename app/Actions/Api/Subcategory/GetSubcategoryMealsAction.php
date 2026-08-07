<?php

namespace App\Actions\Api\Subcategory;

use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class GetSubcategoryMealsAction
{
    public function execute(Subcategory $subcategory, Request $request): LengthAwarePaginator
    {
        $query = $subcategory->meals()->with('category')->available();

        if ($request->has('featured')) {
            $request->boolean('featured') ? $query->featured() : $query->where('is_featured', false);
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock') ? $query->inStock() : $query->outOfStock();
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

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        return $query->paginate($perPage)->withQueryString();
    }
}
