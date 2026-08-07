<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Subcategory\GetSubcategoriesByCategoryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SubcategoryAjaxController extends Controller
{
    public function byCategory(int $categoryId, GetSubcategoriesByCategoryAction $action): JsonResponse
    {
        $subcategories = $action->run($categoryId);

        return response()->json($subcategories);
    }
}
