<?php

namespace App\Actions\Admin\Category;

use App\Models\Category;
use App\Traits\Media;
use Illuminate\Support\Str;

class StoreCategoryAction
{
    use Media;

    public function run(array $data, $imageFile = null): Category
    {
        $imageInput = $imageFile ?: ($data['image_url'] ?? null);
        unset($data['image_file'], $data['image_url']);

        if ($imageInput) {
            $data['image'] = $this->handleImageUpload($imageInput, 'categories');
        }

        $data['slug'] = Str::slug($data['name']);
        
        return Category::create($data);
    }
}
