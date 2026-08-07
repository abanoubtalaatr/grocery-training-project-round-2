<?php

namespace App\Action\Admin\Subcategory;

use App\Models\Subcategory;
use Illuminate\Support\Str;

class CreateSubcategoryAction
{
    public function execute(array $data): Subcategory
    {
        $data['slug'] = Str::slug($data['name']);

        return Subcategory::create($data);
    }
}
