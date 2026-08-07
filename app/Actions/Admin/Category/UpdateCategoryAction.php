<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use App\Traits\Media;
use Illuminate\Support\Str;

class UpdateCategoryAction
{
    use Media;

    public function run(Category $category, array $data, $imageFile = null): Category
    {
        $imageInput = $imageFile ?: ($data['image_url'] ?? null);
        unset($data['image_file'], $data['image_url']);

        if ($imageInput) {
            $data['image'] = $this->handleImageUpload($imageInput, 'categories', $category->image);
        }

        $data['slug'] = Str::slug($data['name']);
        
        $category->update($data);

        return $category;
    }
}
