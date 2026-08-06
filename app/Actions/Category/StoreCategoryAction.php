<?php

namespace App\Actions\Category;

use App\Models\Category;

class StoreCategoryAction
{
    public function execute(array $data): Category
    {
        if (isset($data['image_url'])) {
            $data['image'] = $data['image_url'];
        }

        return Category::create($data);
    }
}
