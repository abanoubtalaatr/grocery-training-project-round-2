<?php

namespace App\Action\Admin\Category;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        if ($category->meals()->count() > 0) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete category with associated meals. Remove or reassign meals first.'],
            ]);
        }

        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }

        $category->subcategories()->delete();

        $category->delete();
    }
}
