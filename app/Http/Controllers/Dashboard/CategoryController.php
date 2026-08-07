<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount(['meals', 'subcategories'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('dashboard.categories.create', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $this->categoryData($request);
        $data['image'] = $this->storeImage($request);

        Category::create($data);

        return redirect()
            ->route('dashboard.categories.index')
            ->with('success', 'تم إنشاء التصنيف بنجاح.');
    }

    public function show(Category $category): View
    {
        $category->loadCount(['meals', 'subcategories']);
        $category->load(['subcategories' => fn ($query) => $query->ordered()->limit(8)]);

        return view('dashboard.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        return view('dashboard.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $this->categoryData($request);

        if ($request->hasFile('image')) {
            $this->deleteImage($category);
            $data['image'] = $this->storeImage($request);
        }

        $category->update($data);

        return redirect()
            ->route('dashboard.categories.show', $category)
            ->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->deleteImage($category);
        $category->delete();

        return redirect()
            ->route('dashboard.categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح.');
    }

    private function categoryData(CategoryRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        unset($data['image']);

        return $data;
    }

    private function storeImage(CategoryRequest $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('categories', 'public');
    }

    private function deleteImage(Category $category): void
    {
        if (!$category->image || Str::startsWith($category->image, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($category->image);
    }
}
