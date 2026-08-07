<?php

namespace App\Action\Admin\Category;

use App\Models\Category;

class ToggleCategoryStatusAction
{
    public function execute(Category $category): void
    {
        $category->update([
            'is_active' => !$category->is_active,
        ]);
    }
}
