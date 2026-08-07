<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteProductImageAction
{
    public function execute(Meal $product, string $imagePath): void
    {
        $images = $product->images ?? [];

        if (!in_array($imagePath, $images)) {
            throw ValidationException::withMessages([
                'image' => ['Image not found'],
            ]);
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        $updatedImages = array_values(array_filter($images, fn ($img) => $img !== $imagePath));

        $product->update([
            'images' => $updatedImages,
        ]);
    }
}
