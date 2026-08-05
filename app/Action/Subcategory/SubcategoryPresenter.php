<?php

namespace App\Action\Subcategory;

class SubcategoryPresenter
{
    public function presentShow($subcategory)
    {
        return [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'slug' => $subcategory->slug,
            'description' => $subcategory->description,
            'image_url' => $subcategory->image_url,
            'order' => $subcategory->order,
            'is_active' => $subcategory->is_active,
            'category' => [
                'id' => $subcategory->category->id,
                'name' => $subcategory->category->name,
                'slug' => $subcategory->category->slug,
            ],
            'meals' => $subcategory->meals->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'image_url' => $meal->image_url,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'is_featured' => $meal->is_featured,
                    'features' => $meal->features,
                ];
            })->values(),
            'meals_count' => $subcategory->meals()->available()->count(),
            'created_at' => $subcategory->created_at,
            'updated_at' => $subcategory->updated_at,
        ];
    }

    public function presentMealsPaging($subcategory, $paginator)
    {
        return [
            'subcategory' => [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
            ],
            'meals' => collect($paginator->items())->map(function ($meal) {
                if (is_array($meal)) return $meal;

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
                    'in_stock' => $meal->isInStock(),
                    'features' => $meal->features,
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
