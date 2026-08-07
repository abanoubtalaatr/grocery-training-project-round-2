<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Api\Category\CategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(protected CategoryAction $action)
    {
    }

    public function index(Request $request): View
    {
        $query = Category::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Sort
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        if (in_array($sort, ['name', 'created_at', 'is_active'])) {
            $query->orderBy($sort, $direction);
        }

        $categories = $query->withCount('meals')->paginate(15);

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->action->store($request);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): View
    {
        return view('admin.categories.show', [
            'category' => $category->load('meals'),
        ]);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->action->update($request, $category);

        return redirect()->route('admin.categories.show', $category)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $this->action->delete($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
