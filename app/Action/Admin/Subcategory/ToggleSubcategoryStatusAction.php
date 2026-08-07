<?php

namespace App\Action\Admin\Subcategory;

use App\Models\Subcategory;

class ToggleSubcategoryStatusAction
{
    public function execute(Subcategory $subcategory): void
    {
        $subcategory->update([
            'is_active' => !$subcategory->is_active,
        ]);
    }
}
