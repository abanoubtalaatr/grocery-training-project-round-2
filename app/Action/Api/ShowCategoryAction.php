<?php

namespace App\Action\Api;

use App\Models\Category;

class ShowCategoryAction
{
    /**
     * Load category with meals (available) ordered desc
     */
    public function execute(Category $category): Category
    {
        return $category->load(['meals' => function ($query) {
            $query->available()->orderBy('created_at', 'desc');
        }]);
    }
}
