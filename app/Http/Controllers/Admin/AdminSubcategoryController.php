<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Subcategory\CreateSubcategoryAction;
use App\Action\Admin\Subcategory\DeleteSubcategoryAction;
use App\Action\Admin\Subcategory\ToggleSubcategoryStatusAction;
use App\Action\Admin\Subcategory\UpdateSubcategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubcategoryRequest;
use App\Http\Requests\Admin\UpdateSubcategoryRequest;
use App\Http\Resources\Admin\SubcategoryResource;
use App\Models\Subcategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminSubcategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $subcategories = Subcategory::with('category:id,name')
            ->withCount('meals')
            ->latest()
            ->get();

        return $this->success(SubcategoryResource::collection($subcategories),'Subcategories retrieved successfully');
    }

    public function store(StoreSubcategoryRequest $request, CreateSubcategoryAction $action): JsonResponse
    {
        $subcategory = $action->execute($request->validated());

        return $this->success(new SubcategoryResource($subcategory->load('category')),'Subcategory created successfully',201);
    }

    public function show(Subcategory $subcategory): JsonResponse
    {
        $subcategory->load('category');
        $subcategory->loadCount('meals');

        return $this->success(new SubcategoryResource($subcategory),'Subcategory retrieved successfully');
    }

    public function update(UpdateSubcategoryRequest $request, Subcategory $subcategory, UpdateSubcategoryAction $action): JsonResponse
    {
        $action->execute($subcategory, $request->validated());

        return $this->success( new SubcategoryResource($subcategory->fresh()->load('category')),'Subcategory updated successfully');
    }

    public function destroy(Subcategory $subcategory, DeleteSubcategoryAction $action): JsonResponse
    {
        $action->execute($subcategory);

        return $this->success(null, 'Subcategory deleted successfully');
    }

    public function toggleStatus(Subcategory $subcategory, ToggleSubcategoryStatusAction $action): JsonResponse
    {
        $action->execute($subcategory);

        return $this->success(new SubcategoryResource($subcategory->fresh()),'Subcategory status updated successfully');
    }
}
