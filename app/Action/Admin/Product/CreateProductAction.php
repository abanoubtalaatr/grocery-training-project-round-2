<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(array $data, ?array $images = null): Meal
    {
        $data['slug'] = Str::slug($data['title']);

        $product = Meal::create($data);

        if ($images) {
            $this->uploadImages($product, $images);
        }

        return $product;
    }

    private function uploadImages(Meal $product, array $images): void
    {
        $uploadedImages = [];
        $existingImages = $product->images ?? [];

        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            $uploadedImages[] = $path;
        }

        $product->update([
            'images' => array_merge($existingImages, $uploadedImages),
        ]);
    }
}
