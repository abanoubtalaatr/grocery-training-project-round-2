<?php

namespace App\Action\Api;

use App\Models\Subcategory;

class ShowSubcategoryAction
{
    public function execute(Subcategory $subcategory)
    {
        return $subcategory->load(['category', 'meals' => function ($query) {
            $query->available()->limit(10);
        }]);
    }
}
