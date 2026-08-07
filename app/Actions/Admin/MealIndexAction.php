<?php

namespace App\Actions\Admin;

use App\Models\Meal;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;

class MealIndexAction
{
    /**
     * Return data needed by the admin meals index.
     * Keeps query logic out of the controller.
     */
    public function execute(Request $request): array
    {
        $query = Meal::with(['category', 'subcategory']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'in_stock') {
                $query->inStock();
            } elseif ($status === 'out_of_stock') {
                $query->outOfStock();
            }
        }

        if ($request->filled('availability')) {
            $available = $request->boolean('availability');
            $query->where('is_available', $available);
        }

        if ($request->filled('featured')) {
            $featured = $request->boolean('featured');
            $query->where('is_featured', $featured);
        }

        if ($request->filled('is_hot')) {
            $hot = $request->boolean('is_hot');
            $query->where('is_hot', $hot);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'price') {
            $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
        } elseif ($sortBy === 'stock') {
            $query->orderBy('stock_quantity', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->orderBy('rating', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $meals = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $offers = Offer::active()->get();

        return compact('meals', 'categories', 'offers');
    }
}
