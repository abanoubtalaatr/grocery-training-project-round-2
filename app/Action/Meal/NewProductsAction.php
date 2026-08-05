<?php

namespace App\Action\Meal;

use App\Models\Meal;

class NewProductsAction
{
    public function handle(array $filters = [])
    {
        $query = Meal::with('category')->available()->orderBy('created_at', 'desc');
        if (!empty($filters['limit'])) $query->limit((int) $filters['limit']);
        return $query->get();
    }
}
