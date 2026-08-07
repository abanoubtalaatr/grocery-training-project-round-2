<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Actions\Admin\MealIndexAction;
use App\Actions\Admin\MealShowAction;
use App\Actions\Admin\MealStoreAction;
use App\Actions\Admin\MealUpdateAction;
use App\Actions\Admin\MealDeleteAction;
use App\Http\Requests\Admin\StoreMealRequest;
use App\Http\Requests\Admin\UpdateMealRequest;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MealController extends Controller
{
    public function __construct(
        protected MealIndexAction $mealIndexAction,
        protected MealShowAction $mealShowAction,
        protected MealStoreAction $mealStoreAction,
        protected MealUpdateAction $mealUpdateAction,
        protected MealDeleteAction $mealDeleteAction
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of meals.
     */
    public function index(Request $request): View
    {
        $data = $this->mealIndexAction->execute($request);

        return view('admin.meals.index', $data);
    }

    /**
     * Store a newly created meal in storage.
     */
    public function store(StoreMealRequest $request): RedirectResponse
    {
        $meal = $this->mealStoreAction->execute($request->validated());

        return redirect()->route('admin.meals.show', $meal)
            ->with('success', 'Meal created successfully.');
    }

    /**
     * Display the specified meal.
     */
    public function show(Meal $meal): View
    {
        $meal = $this->mealShowAction->execute($meal);

        return view('admin.meals.show', compact('meal'));
    }

    /**
     * Update the specified meal in storage.
     */
    public function update(UpdateMealRequest $request, Meal $meal): RedirectResponse
    {
        $this->mealUpdateAction->execute($meal, $request->validated());

        return redirect()->route('admin.meals.show', $meal)
            ->with('success', 'Meal updated successfully.');
    }

    /**
     * Remove the specified meal from storage.
     */
    public function destroy(Meal $meal): RedirectResponse
    {
        $this->mealDeleteAction->execute($meal);

        return redirect()->route('admin.meals.index')
            ->with('success', 'Meal deleted successfully.');
    }
}
