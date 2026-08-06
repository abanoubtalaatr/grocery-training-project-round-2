<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class GetAllCategoriesAction
{
    public function execute(): Collection
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }
}
