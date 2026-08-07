<?php

namespace App\Actions\Web\Category;

use App\Models\Category;
use Illuminate\Support\Str;

class UpdateCategoryAction
{
    public function handle(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $category->update($data);
        
        return $category;
    }
}
