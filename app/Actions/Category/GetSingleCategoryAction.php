<?php

namespace App\Actions\Category;

use App\Models\Category;

class GetSingleCategoryAction
{
    public function execute(string $id): Category
    {
        return Category::with(['meals' => fn ($query) => $query->available()->latest()])
            ->findOrFail($id);
    }
}
