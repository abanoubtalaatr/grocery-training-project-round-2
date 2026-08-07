<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;
use Illuminate\Support\Facades\Storage;

class DeleteProductAction
{
    public function execute(Meal $product): void
    {
        if ($product->images) {
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->favorites()->delete();

        $product->cartItems()->delete();

        $product->delete();
    }
}
