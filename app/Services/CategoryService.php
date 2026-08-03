<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Interfaces\CategoryServiceInterface;
class CategoryService implements CategoryServiceInterface
{
    public function index()
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }

    public function show(string $id)
    {
        return Category::with([
            'meals' => fn ($query) => $query->available()->latest()
        ])->findOrFail($id);
    }

    public function meals(string $id, Request $request): array
    {
        $category = Category::findOrFail($id);

        $query = $category->meals()
            ->with('subcategory')
            ->available();

        if ($request->has('featured')) {
            $request->boolean('featured')
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock')
                ? $query->inStock()
                : $query->outOfStock();
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc'
            ? 'asc'
            : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowedSortFields = [
            'created_at',
            'price',
            'rating',
            'title',
            'sold_count',
        ];

        if (in_array($sortBy, $allowedSortFields)) {

            if ($sortBy === 'price') {
                $query->orderByRaw(
                    'COALESCE(discount_price, price) ' . $sortOrder
                );
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

        } else {
            $query->latest();
        }

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            50
        );

        return [
            'category' => $category,
            'paginator' => $query->paginate($perPage),
        ];
    }
}