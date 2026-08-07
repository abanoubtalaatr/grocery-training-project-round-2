<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetCategoriesAction
{
    public function execute()
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }
}