<?php

namespace App\Action\Category;

use App\Models\Order;

class CategoryPresenter
{
    public function presentCategoryWithMeals($category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $category->image_url,
            'sort_order' => $category->sort_order,
            'meals' => $category->meals->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'has_offer' => $meal->hasOffer(),
                    'is_featured' => $meal->is_featured,
                    'features' => $meal->features,
                ];
            })->values(),
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    }

    public function presentMealPaging($category, $paginator): array
    {
        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'meals' => collect($paginator->items())->map(function ($meal) {
                // if items are models they are formatted already by the action consumer; assume array or model
                if (is_array($meal)) return $meal;

                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'description' => $meal->description,
                    'image_url' => $meal->image_url,
                    'offer_title' => $meal->offer_title,
                    ...$meal->getApiPriceAttributes(),
                    'has_offer' => $meal->hasOffer(),
                    'rating' => (float) $meal->rating,
                    'rating_count' => (int) $meal->rating_count,
                    'size' => $meal->size,
                    'brand' => $meal->brand,
                    'stock_quantity' => $meal->stock_quantity,
                    'in_stock' => $meal->isInStock(),
                    'is_featured' => $meal->is_featured,
                    'expiry_date' => $meal->expiry_date,
                    'days_until_expiry' => $meal->daysUntilExpiry(),
                    'is_expired' => $meal->isExpired(),
                    'features' => $meal->features,
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                        'slug' => $meal->subcategory->slug,
                    ] : null,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }
}
