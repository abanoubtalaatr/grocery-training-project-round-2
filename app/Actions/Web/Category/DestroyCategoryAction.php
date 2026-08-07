<?php

namespace App\Actions\Web\Category;

use App\Models\Category;

class DestroyCategoryAction
{
    public function handle(Category $category): void
    {
        $category->delete();
    }
}
