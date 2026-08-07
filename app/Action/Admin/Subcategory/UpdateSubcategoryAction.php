<?php

namespace App\Action\Admin\Subcategory;

use App\Models\Subcategory;
use Illuminate\Support\Str;

class UpdateSubcategoryAction
{
    public function execute(Subcategory $subcategory, array $data): void
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $subcategory->update($data);
    }
}
