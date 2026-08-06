<?php

namespace App\Actions\Api\Category;

use App\Models\Category;

class ShowCategoryAction
{
    public function run(Category $category): Category
    {
        return $category->load([
            'meals' => function ($query) {
                $query->available()
                    ->latest();
            }
        ]);
    }
}