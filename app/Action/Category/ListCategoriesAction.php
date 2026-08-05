<?php

namespace App\Action\Category;

use App\Models\Category;

class ListCategoriesAction
{
    public function handle()
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('meals')
            ->select(['id', 'name', 'slug', 'description', 'image', 'sort_order', 'created_at'])
            ->get();

        return $categories->map(fn($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $category->image_url,
            'meals_count' => $category->meals_count,
            'sort_order' => $category->sort_order,
            'created_at' => $category->created_at,
        ]);
    }
}
