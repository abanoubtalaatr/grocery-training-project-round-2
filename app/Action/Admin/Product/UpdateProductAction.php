<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;
use Illuminate\Support\Str;

class UpdateProductAction
{
    public function execute(Meal $product, array $data): void
    {
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $product->update($data);
    }
}
