<?php

namespace App\Actions\Web\Category;

use App\Models\Category;
use Illuminate\Support\Str;

class StoreCategoryAction
{
    public function handle(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);
        
        return Category::create($data);
    }
}
