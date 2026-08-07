<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;

class ToggleProductAvailabilityAction
{
    public function execute(Meal $product): void
    {
        $product->update([
            'is_available' => !$product->is_available,
        ]);
    }
}
