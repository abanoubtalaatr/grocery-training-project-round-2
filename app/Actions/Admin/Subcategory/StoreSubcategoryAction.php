<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;
use App\Traits\Media;
use Illuminate\Support\Str;

class StoreSubcategoryAction
{
    use Media;

    public function run(array $data, $imageFile = null): Subcategory
    {
        $imageInput = $imageFile ?: ($data['image_url'] ?? null);
        unset($data['image_file'], $data['image_url']);

        if ($imageInput) {
            $data['image_url'] = $this->handleImageUpload($imageInput, 'subcategories');
        }

        $data['slug'] = Str::slug($data['name']);
        
        return Subcategory::create($data);
    }
}
