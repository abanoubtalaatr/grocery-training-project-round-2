<?php

namespace App\Action\Api;

use App\Models\Category;

class GetCategoriesAction
{
    /**
     * Return collection of active ordered categories with meals count
     */
    public function execute()
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }
}
