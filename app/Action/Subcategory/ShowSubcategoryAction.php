<?php

namespace App\Action\Subcategory;

use App\Models\Subcategory;

class ShowSubcategoryAction
{
    public function handle(string $id)
    {
        return Subcategory::with(['category:id,name,slug', 'meals' => function ($q) { $q->available()->limit(10); }])
            ->select(['id', 'name', 'slug', 'description', 'image', 'order', 'is_active', 'category_id', 'created_at', 'updated_at'])
            ->findOrFail($id);
    }
}
