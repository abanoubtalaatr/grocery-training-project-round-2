<?php

namespace App\Action\Api;

use App\Models\Category;
use Illuminate\Support\Str;

class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        return Category::create([
            ...$data,
            'slug' => Str::slug($data['name']),
        ]);
    }
}