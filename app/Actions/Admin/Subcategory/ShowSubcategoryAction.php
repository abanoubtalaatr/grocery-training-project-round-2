<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;

class ShowSubcategoryAction
{
    public function run(Subcategory $subcategory): Subcategory
    {
        return $subcategory->load(['category', 'meals']);
    }
}
