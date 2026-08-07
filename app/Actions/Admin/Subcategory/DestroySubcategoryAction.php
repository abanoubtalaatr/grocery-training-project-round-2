<?php

namespace App\Actions\Admin\Subcategory;

use App\Models\Subcategory;

class DestroySubcategoryAction
{
    public function run(Subcategory $subcategory): void
    {
        $subcategory->delete();
    }
}
