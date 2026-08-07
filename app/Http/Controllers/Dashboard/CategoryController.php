<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Category\DestroyCategoryAction;
use App\Actions\Admin\Category\IndexCategoryAction;
use App\Actions\Admin\Category\ShowCategoryAction;
use App\Actions\Admin\Category\StoreCategoryAction;
use App\Actions\Admin\Category\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected $indexAction;
    protected $storeAction;
    protected $showAction;
    protected $updateAction;
    protected $destroyAction;

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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = $this->indexAction->run($request->only('search', 'status'));

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->storeAction->run(
            $request->validated(),
            $request->file('image_file')
        );

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $this->authorize('view', $category);

        $category = $this->showAction->run($category);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->updateAction->run(
            $category,
            $request->validated(),
            $request->file('image_file')
        );

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->destroyAction->run($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
