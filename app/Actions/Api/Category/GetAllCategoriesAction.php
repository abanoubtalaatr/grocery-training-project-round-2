<?php

namespace App\Actions\Api\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class GetAllCategoriesAction
{
    public function run(): Collection
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }
}