<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Meal\DestroyMealAction;
use App\Actions\Admin\Meal\IndexMealAction;
use App\Actions\Admin\Meal\StoreMealAction;
use App\Actions\Admin\Meal\UpdateMealAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Meal\StoreMealRequest;
use App\Http\Requests\Admin\Meal\UpdateMealRequest;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealController extends Controller
{
    public function index(Request $request, IndexMealAction $action): View
    {
        $this->authorize('viewAny', Meal::class);
        $meals = $action->run($request);
        $categories = Category::orderBy('name')->get();

        return view('admin.meals.index', compact('meals', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Meal::class);
        $categories    = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();

        return view('admin.meals.create', compact('categories', 'subcategories'));
    }

    public function store(StoreMealRequest $request, StoreMealAction $action): RedirectResponse
    {
        $this->authorize('create', Meal::class);
        $action->run($request->validated(), $request->file('image_file'));

        return redirect()->route('admin.meals.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Meal $meal): View
    {
        $this->authorize('view', $meal);
        $meal->load(['category', 'subcategory', 'reviews']);

        return view('admin.meals.show', compact('meal'));
    }

    public function edit(Meal $meal): View
    {
        $this->authorize('update', $meal);
        $categories    = Category::orderBy('name')->get();
        $subcategories = Subcategory::where('category_id', $meal->category_id)->orderBy('name')->get();

        return view('admin.meals.edit', compact('meal', 'categories', 'subcategories'));
    }

    public function update(UpdateMealRequest $request, Meal $meal, UpdateMealAction $action): RedirectResponse
    {
        $this->authorize('update', $meal);
        $action->run($meal, $request->validated(), $request->file('image_file'));

        return redirect()->route('admin.meals.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Meal $meal, DestroyMealAction $action): RedirectResponse
    {
        $this->authorize('delete', $meal);
        $action->run($meal);

        return redirect()->route('admin.meals.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('create', Meal::class);
        $meal = Meal::withTrashed()->findOrFail($id);
        $meal->restore();

        return redirect()->route('admin.meals.index')
            ->with('success', 'Product restored successfully.');
    }
}
