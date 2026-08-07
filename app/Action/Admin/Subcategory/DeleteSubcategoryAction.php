<?php

namespace App\Action\Admin\Subcategory;

use App\Models\Subcategory;
use Illuminate\Validation\ValidationException;

class DeleteSubcategoryAction
{
    public function execute(Subcategory $subcategory): void
    {
        if ($subcategory->meals()->count() > 0) {
            throw ValidationException::withMessages([
                'subcategory' => ['Cannot delete subcategory with associated meals. Remove or reassign meals first.'],
            ]);
        }

        $subcategory->delete();
    }
}
