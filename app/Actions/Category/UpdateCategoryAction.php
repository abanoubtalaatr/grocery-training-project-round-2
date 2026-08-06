<?php

namespace App\Actions\Category;

use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        if (isset($data['image_url'])) {
            $data['image'] = $data['image_url'];
        }

        $category->update($data);

        return $category->fresh();
    }
}
