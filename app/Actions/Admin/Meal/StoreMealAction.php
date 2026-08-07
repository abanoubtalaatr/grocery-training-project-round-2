<?php

namespace App\Actions\Admin\Meal;

use App\Models\Meal;
use App\Traits\Media;
use Illuminate\Support\Str;

class StoreMealAction
{
    use Media;

    public function run(array $data, $imageFile = null): Meal
    {
        $imageInput = $imageFile ?: ($data['image'] ?? null);
        unset($data['image_file']);

        if ($imageInput && is_object($imageInput)) {
            $data['image'] = $this->handleImageUpload($imageInput, 'meals');
        } elseif (!empty($data['image']) && is_string($data['image'])) {
            // keep as-is (URL string)
        } else {
            unset($data['image']);
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Cast booleans
        $data['is_featured'] = isset($data['is_featured']) ? (bool) $data['is_featured'] : false;
        $data['is_available'] = isset($data['is_available']) ? (bool) $data['is_available'] : false;
        $data['is_hot']       = isset($data['is_hot'])       ? (bool) $data['is_hot']       : false;

        // Nullify empty numeric fields
        foreach (['discount_price', 'stock_quantity', 'rating', 'rating_count'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return Meal::create($data);
    }
}
