<?php

namespace App\Action\Api;

use App\Models\Category;
use Illuminate\Support\Str;

class UpdateCategoryAction
{
    public function execute(Category $category, array $data): void
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);
    }
}