<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use App\Models\Offer;
use Illuminate\View\View;

class MealFormController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::active()->ordered()->get();
        $offers = Offer::active()->get();

        return view('admin.meals.create', compact('categories', 'subcategories', 'offers'));
    }

    public function edit(Meal $meal): View
    {
        $meal->load(['category', 'subcategory']);
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::active()->ordered()->get();
        $offers = Offer::active()->get();

        return view('admin.meals.edit', compact('meal', 'categories', 'subcategories', 'offers'));
    }
}
