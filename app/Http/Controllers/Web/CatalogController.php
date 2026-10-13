<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function categories(): View
    {
        $categories = Category::active()
            ->ordered()
            ->withCount([
                'meals' => fn ($q) => $q->available(),
            ])
            ->get();

        return view('catalog.categories', ['categories' => $categories]);
    }

    public function category(Category $category): View
    {
        if (! $category->is_active) {
            abort(404);
        }

        $category->loadCount([
            'meals' => fn ($q) => $q->available(),
        ]);

        $subcategories = $category->subcategories()
            ->active()
            ->ordered()
            ->get();

        $meals = $category->meals()
            ->with('subcategory')
            ->available()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('catalog.category', [
            'category' => $category,
            'subcategories' => $subcategories,
            'meals' => $meals,
        ]);
    }

    public function subcategory(Subcategory $subcategory): View
    {
        if (! $subcategory->is_active) {
            abort(404);
        }

        $subcategory->load('category');
        $subcategory->loadCount([
            'meals' => fn ($q) => $q->available(),
        ]);

        $meals = $subcategory->meals()
            ->with('category')
            ->available()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('catalog.subcategory', [
            'subcategory' => $subcategory,
            'meals' => $meals,
        ]);
    }

    public function meals(Request $request): View
    {
        $query = Meal::with(['category', 'subcategory'])->available();

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        $meals = $query->paginate(15)->withQueryString();

        $categories = Category::active()->ordered()->get();

        return view('catalog.meals', [
            'meals' => $meals,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'min_price', 'max_price', 'sort_by']),
        ]);
    }

    public function meal(Meal $meal): View
    {
        if (! $meal->is_available) {
            abort(404);
        }

        $meal->load([
            'category',
            'subcategory',
            'reviews' => fn ($q) => $q->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc'),
        ]);

        $relatedMeals = Meal::with('category')
            ->available()
            ->where('category_id', $meal->category_id)
            ->where('id', '!=', $meal->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $userSmartLists = [];
        $isFavorited = false;

        if (auth()->check()) {
            $user = auth()->user();
            $isFavorited = $user->favorites()->where('meal_id', $meal->id)->exists();
            $userSmartLists = $user->smartLists()->withCount('meals')->get();
        }

        return view('catalog.meal', [
            'meal' => $meal,
            'relatedMeals' => $relatedMeals,
            'isFavorited' => $isFavorited,
            'userSmartLists' => $userSmartLists,
        ]);
    }

    public function search(Request $request): View
    {
        $search = $request->input('search', '');

        $query = Meal::with(['category', 'subcategory'])->available();

        $query = $this->applyFilters($query, $request);
        $query = $this->applySorting($query, $request);

        $meals = $query->paginate(15)->withQueryString();

        return view('catalog.search', [
            'meals' => $meals,
            'search' => $search,
        ]);
    }

    public function recommendations(): View
    {
        $limit = 12;

        $featuredMeals = Meal::with('category')
            ->available()
            ->featured()
            ->whereNotNull('discount_price')
            ->inRandomOrder()
            ->limit((int) ceil($limit / 2))
            ->get();

        $randomMeals = Meal::with('category')
            ->available()
            ->whereNotIn('id', $featuredMeals->pluck('id'))
            ->inRandomOrder()
            ->limit($limit - $featuredMeals->count())
            ->get();

        $meals = $featuredMeals->merge($randomMeals)->shuffle()->take($limit);

        return view('catalog.recommendations', ['meals' => $meals]);
    }

    public function todayDeals(): View
    {
        $meals = Meal::with('category')
            ->available()
            ->withActiveDiscount()
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('catalog.today-deals', ['meals' => $meals]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->input('subcategory_id'));
        }

        if ($request->has('min_price')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->input('min_price')]);
        }

        if ($request->has('max_price')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->input('max_price')]);
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->input('min_rating'));
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }

        return $query;
    }

    private function applySorting($query, Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowedSortFields = ['created_at', 'price', 'rating', 'title', 'sold_count'];

        if (in_array($sortBy, $allowedSortFields)) {
            if ($sortBy === 'price') {
                $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}
