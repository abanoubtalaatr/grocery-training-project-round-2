@extends('layouts.admin')

@section('title', 'Meals / Products')
@section('breadcrumb', 'Meals / Products')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Product Management</h1>
    @can('create', App\Models\Meal::class)
        <a href="{{ route('admin.meals.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add New Product
        </a>
    @endcan
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.meals.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search Products</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Title or brand...">
        </div>
        <div class="form-group" style="width: 180px; margin-bottom: 0;">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="width: 180px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Deleted</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'category_id', 'status']))
                <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Badges</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meals as $meal)
                    <tr @if($meal->trashed()) style="opacity: 0.6;" @endif>
                        <td>
                            <img src="{{ $meal->image_url ?? 'https://placehold.co/44x44/eee/999?text=N/A' }}"
                                 alt="{{ $meal->title }}"
                                 style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color);">
                        </td>
                        <td>
                            <span style="font-weight: 600; color: var(--text-primary);">{{ $meal->title }}</span>
                            @if($meal->brand)
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $meal->brand }}</p>
                            @endif
                            @if($meal->trashed())
                                <span class="badge badge-danger" style="font-size: 0.65rem; margin-top: 3px;">DELETED</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">{{ $meal->category->name ?? '—' }}</span>
                            @if($meal->subcategory)
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $meal->subcategory->name }}</p>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 700; color: var(--color-primary);">${{ number_format($meal->final_price, 2) }}</span>
                            @if($meal->getRawDiscountPrice())
                                <p style="font-size: 0.75rem; color: var(--text-muted); text-decoration: line-through; margin: 0;">${{ number_format($meal->price, 2) }}</p>
                            @endif
                        </td>
                        <td>
                            @if($meal->stock_quantity <= 0)
                                <span class="badge badge-danger">Out of Stock</span>
                            @elseif($meal->stock_quantity <= 10)
                                <span class="badge badge-warning">{{ $meal->stock_quantity }} left</span>
                            @else
                                <span style="font-weight: 600; color: var(--color-success);">{{ $meal->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>
                            @if($meal->is_available)
                                <span class="badge badge-success">Available</span>
                            @else
                                <span class="badge badge-secondary">Unavailable</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                @if($meal->is_featured)
                                    <span class="badge badge-info" style="font-size: 0.65rem;">Featured</span>
                                @endif
                                @if($meal->is_hot)
                                    <span class="badge badge-warning" style="font-size: 0.65rem;">Hot</span>
                                @endif
                                @if($meal->hasOffer())
                                    <span class="badge badge-success" style="font-size: 0.65rem;">On Sale</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; justify-content: flex-end;">
                                @if(!$meal->trashed())
                                    <a href="{{ route('admin.meals.show', $meal->id) }}" class="btn btn-secondary btn-sm" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('update', $meal)
                                        <a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $meal)
                                        <form action="{{ route('admin.meals.destroy', $meal->id) }}" method="POST" onsubmit="return confirm('Soft-delete this product?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <form action="{{ route('admin.meals.restore', $meal->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Restore">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-bowl-food"></i>
                            <p>No products found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($meals->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $meals->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
// Dynamically load subcategories based on selected category
document.getElementById('category_id')?.addEventListener('change', function() {
    // Handled server-side via filter reload
});
</script>
@endsection
