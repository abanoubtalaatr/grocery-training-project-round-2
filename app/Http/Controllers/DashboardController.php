<?php

namespace App\Http\Controllers;

use App\Actions\Web\Category\DestroyCategoryAction;
use App\Actions\Web\Category\StoreCategoryAction;
use App\Actions\Web\Category\UpdateCategoryAction;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StoreCategoryAction $storeAction,
        private UpdateCategoryAction $updateAction,
        private DestroyCategoryAction $destroyAction
    ) {
    }

    public function index(Request $request): View
    {
        $summary = [
            'total_categories' => Category::count(),
            'active_categories' => Category::active()->count(),
            'total_meals' => Meal::count(),
            'available_meals' => Meal::available()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::active()->count(),
            'total_users' => User::count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total'),
        ];

        $categories = Category::withCount('meals')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $topMeals = Meal::available()
            ->with('category')
            ->orderByDesc('sold_count')
            ->limit(8)
            ->get();

        $recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentUsers = User::orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('summary', 'categories', 'topMeals', 'recentOrders', 'recentUsers'));
    }

    public function storeCategory(StoreCategoryRequest $request): RedirectResponse
    {
        $this->storeAction->handle($request->validated());

        return redirect()->route('dashboard.index')->with('success', 'تم إضافة الفئة بنجاح.');
    }

    public function updateCategory(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->updateAction->handle($category, $request->validated());

        return redirect()->route('dashboard.index')->with('success', 'تم تحديث الفئة بنجاح.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $this->destroyAction->handle($category);

        return redirect()->route('dashboard.index')->with('success', 'تم حذف الفئة بنجاح.');
    }
}
