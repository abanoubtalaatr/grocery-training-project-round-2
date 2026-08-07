<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Actions\Category\IndexCategoryAction;
use App\Actions\Category\StoreCategoryAction;
use App\Actions\Category\ShowCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\Actions\Category\DestroyCategoryAction;

class CategoryController extends Controller
{
    private IndexCategoryAction $indexAction;
    private StoreCategoryAction $storeAction;
    private ShowCategoryAction $showAction;
    private UpdateCategoryAction $updateAction;
    private DestroyCategoryAction $destroyAction;

    public function __construct(
        IndexCategoryAction $indexAction,
        StoreCategoryAction $storeAction,
        ShowCategoryAction $showAction,
        UpdateCategoryAction $updateAction,
        DestroyCategoryAction $destroyAction
    ) {
        $this->indexAction = $indexAction;
        $this->storeAction = $storeAction;
        $this->showAction = $showAction;
        $this->updateAction = $updateAction;
        $this->destroyAction = $destroyAction;
    }

    public function index(Request $request)
    {
        return $this->indexAction->handle($request);
    }

    public function store(StoreCategoryRequest $request)
    {
        return $this->storeAction->handle($request);
    }

    public function show(Category $category)
    {
        return $this->showAction->handle($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        return $this->updateAction->handle($request, $category);
    }

    public function destroy(Category $category)
    {
        return $this->destroyAction->handle($category);
    }
}