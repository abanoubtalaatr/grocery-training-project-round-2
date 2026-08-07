<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;

class ShowCategoryAction
{
    public function run(Category $category): Category
    {
        return $category->load([
            'subcategories' => function($q) {
                $q->orderBy('order', 'asc');
            },
            'meals' => function($q) {
                $q->latest();
            }
        ]);
    }
}
