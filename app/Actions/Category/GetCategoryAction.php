<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetCategoryAction
{
    public function execute(string $id): Category
    {
        return Category::with([
            'meals' => function ($query) {
                $query->available()->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);
    }
}