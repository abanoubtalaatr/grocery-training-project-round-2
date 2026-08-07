<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Actions\Api\Category\IndexCategoryAction;
use App\Actions\Api\Category\StoreCategoryAction;
use App\Actions\Api\Category\ShowCategoryAction;
use App\Actions\Api\Category\UpdateCategoryAction;
use App\Actions\Api\Category\DestroyCategoryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function index(Request $request): JsonResponse
    {
        return $this->indexAction->handle($request);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storeAction->handle($request);
    }

    public function show($id): JsonResponse
    {
        return $this->showAction->handle($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->updateAction->handle($id, $request);
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->destroyAction->handle($id);
    }
}
