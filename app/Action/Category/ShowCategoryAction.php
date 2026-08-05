<?php

namespace App\Action\Category;

use App\Models\Category;

class ShowCategoryAction
{
    public function handle(string $id)
    {
        $category = Category::with(['meals' => function ($query) {
            $query->available()->orderBy('created_at', 'desc')
                ->select(['id', 'title', 'slug', 'description', 'image', 'offer_title', 'rating', 'rating_count', 'is_featured', 'size', 'brand', 'stock_quantity', 'expiry_date', 'subcategory_id']);
        }])->findOrFail($id);

        return $category;
    }
}
