<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;
use Illuminate\Support\Facades\Storage;

class UploadProductImagesAction
{
    public function execute(Meal $product, array $images): array
    {
        $uploadedImages = [];
        $existingImages = $product->images ?? [];

        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            $uploadedImages[] = $path;
        }

        $allImages = array_merge($existingImages, $uploadedImages);
        
        $product->update([
            'images' => $allImages,
        ]);

        return $uploadedImages;
    }
}
