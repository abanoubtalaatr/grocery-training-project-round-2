<?php

namespace App\Action\Api;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class GetSubcategoriesAction
{
    public function execute(Request $request)
    {
        $query = Subcategory::with('category')->active();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        return $query->inRandomOrder()->get();
    }
}
