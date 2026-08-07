<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;
use App\Traits\Media;
use Illuminate\Support\Str;

class UpdateSubcategoryAction
{
    use Media;

    public function run(Subcategory $subcategory, array $data, $imageFile = null): Subcategory
    {
        $imageInput = $imageFile ?: ($data['image_url'] ?? null);
        unset($data['image_file'], $data['image_url']);

        // Since the database column is 'image_url' for subcategories, we read attributes['image_url']
        $oldImagePath = $subcategory->getRawOriginal('image_url');

        if ($imageInput) {
            $data['image_url'] = $this->handleImageUpload($imageInput, 'subcategories', $oldImagePath);
        }

        $data['slug'] = Str::slug($data['name']);
        
        $subcategory->update($data);

        return $subcategory;
    }
}
