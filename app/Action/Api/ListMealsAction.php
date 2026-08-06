<?php

namespace App\Action\Api;

use App\Models\Meal;
use Illuminate\Http\Request;

class ListMealsAction
{
    public function execute(Request $request, $user = null)
    {
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
        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) '.$sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $meals = $query->get();

        // attach favorites if user provided
        $favoriteMealIds = [];
        if ($user) {
            $favoriteMealIds = $user->favorites()->pluck('meal_id')->toArray();
        }

        return compact('meals', 'favoriteMealIds');
    }
}
