<?php

namespace App\Action\Subcategory;

use App\Models\Subcategory;

class ListSubcategoriesAction
{
    public function handle(?int $categoryId = null)
    {
        $query = Subcategory::with('category:id,name,slug')->active()->select(['id', 'name', 'slug', 'description', 'image', 'order', 'category_id', 'created_at']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->inRandomOrder()->get()->map(function ($subcategory) {
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
                'description' => $subcategory->description,
                'image_url' => $subcategory->image_url,
                'order' => $subcategory->order,
                'category' => [
                    'id' => $subcategory->category->id,
                    'name' => $subcategory->category->name,
                    'slug' => $subcategory->category->slug,
                ],
                'meals_count' => $subcategory->meals()->available()->count(),
                'created_at' => $subcategory->created_at,
            ];
        });
    }
}
