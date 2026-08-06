<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetNewProductsAction
{
    public function execute()
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}