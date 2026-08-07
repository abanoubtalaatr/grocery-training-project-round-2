<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Subcategory\DestroySubcategoryAction;
use App\Actions\Admin\Subcategory\IndexSubcategoryAction;
use App\Actions\Admin\Subcategory\ShowSubcategoryAction;
use App\Actions\Admin\Subcategory\StoreSubcategoryAction;
use App\Actions\Admin\Subcategory\UpdateSubcategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Subcategory\StoreSubcategoryRequest;
use App\Http\Requests\Admin\Subcategory\UpdateSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function index(Request $request, IndexSubcategoryAction $action): View
    {
        $this->authorize('viewAny', Subcategory::class);
        $subcategories = $action->run($request->only('search', 'category_id', 'status'));

        return view('admin.subcategories.index', compact('subcategories'));
    }

    public function create(): View
    {
        $this->authorize('create', Subcategory::class);
        $categories = Category::orderBy('name')->get();

        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(StoreSubcategoryRequest $request, StoreSubcategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Subcategory::class);
        $action->run($request->validated(), $request->file('image_file'));

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory created successfully.');
    }

    public function show(Subcategory $subcategory, ShowSubcategoryAction $action): View
    {
        $this->authorize('view', $subcategory);
        $subcategory = $action->run($subcategory);

        return view('admin.subcategories.show', compact('subcategory'));
    }

    public function edit(Subcategory $subcategory): View
    {
        $this->authorize('update', $subcategory);
        $categories = Category::orderBy('name')->get();

        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(UpdateSubcategoryRequest $request, Subcategory $subcategory, UpdateSubcategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $subcategory);
        $action->run($subcategory, $request->validated(), $request->file('image_file'));

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory updated successfully.');
    }

    public function destroy(Subcategory $subcategory, DestroySubcategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $subcategory);
        $action->run($subcategory);

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory deleted successfully.');
    }
}
