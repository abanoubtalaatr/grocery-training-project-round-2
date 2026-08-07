<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;

class ToggleProductFeaturedAction
{
    public function execute(Meal $product): void
    {
        $product->update([
            'is_featured' => !$product->is_featured,
        ]);
    }
}
