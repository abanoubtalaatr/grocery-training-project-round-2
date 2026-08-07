<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;

class DestroyCategoryAction
{
    public function run(Category $category): void
    {
        $category->delete();
    }
}
