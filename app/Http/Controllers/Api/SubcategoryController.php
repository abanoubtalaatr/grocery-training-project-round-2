<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Subcategory\GetAllSubcategoriesAction;
use App\Actions\Api\Subcategory\GetSubcategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SubcategoryDetailsResource;
use App\Http\Resources\Api\SubcategoryResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    use ApiTrait;

    /**
     * Display a listing of subcategories.
     */
    public function index(Request $request, GetAllSubcategoriesAction $action): JsonResponse
    {
        $subcategories = $action->run($request);

        return $this->dataResponse(
            SubcategoryResource::collection($subcategories),
            'Subcategories retrieved successfully'
        );
    }

    /**
     * Display the specified subcategory.
     */
    public function show(int $id, GetSubcategoryAction $action): JsonResponse
    {
        $subcategory = $action->run($id);

        return $this->dataResponse(
            new SubcategoryDetailsResource($subcategory),
            'Subcategory retrieved successfully'
        );
    }
}