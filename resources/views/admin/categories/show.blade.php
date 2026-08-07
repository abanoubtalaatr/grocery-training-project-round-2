@extends('layouts.admin')

@section('title', 'Category Details')
@section('breadcrumb', 'Categories / Details')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Category: {{ $category->name }}</h1>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </a>
        @can('update', $category)
            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit Category
            </a>
        @endcan
    </div>
</div>

<!-- Info Cards Grid -->
<div style="display: grid; grid-template-columns: 280px 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Image Card -->
    <div class="table-container" style="padding: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px;">
        <img src="{{ $category->image_url }}" alt="Category Image" style="width: 180px; height: 180px; border-radius: 12px; object-fit: cover; border: 1px solid var(--border-color);">
        <div>
            @if($category->is_active)
                <span class="badge badge-success" style="font-size: 0.85rem;">Active</span>
            @else
                <span class="badge badge-danger" style="font-size: 0.85rem;">Inactive</span>
            @endif
        </div>
    </div>

    <!-- Metadata Card -->
    <div class="table-container" style="padding: 25px; display: flex; flex-direction: column; gap: 15px;">
        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            Category Metadata
        </h3>
        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 15px; font-size: 0.95rem;">
            <div style="font-weight: 600; color: var(--text-muted);">Slug:</div>
            <div><code>{{ $category->slug }}</code></div>

            <div style="font-weight: 600; color: var(--text-muted);">Sort Order:</div>
            <div style="font-weight: 700;">{{ $category->sort_order }}</div>

            <div style="font-weight: 600; color: var(--text-muted);">Created At:</div>
            <div>{{ $category->created_at->format('M d, Y - h:i A') }}</div>

            <div style="font-weight: 600; color: var(--text-muted);">Last Updated:</div>
            <div>{{ $category->updated_at->format('M d, Y - h:i A') }}</div>

            <div style="font-weight: 600; color: var(--text-muted); grid-column: 1 / -1;">Description:</div>
            <div style="grid-column: 1 / -1; color: var(--text-muted); background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); line-height: 1.5;">
                {{ $category->description ?: 'No description provided.' }}
            </div>
        </div>
    </div>
</div>

<!-- Relationship Tables (Subcategories & Meals) -->
<div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
    <!-- Subcategories inside Category -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Subcategories ({{ $category->subcategories->count() }})</h3>
            <a href="{{ route('admin.subcategories.create', ['category_id' => $category->id]) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Add Subcategory
            </a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($category->subcategories as $sub)
                        <tr>
                            <td>
                                <img src="{{ $sub->image_url ?? 'https://via.placeholder.com/40' }}" alt="Subcategory" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover;">
                            </td>
                            <td style="font-weight: 600;">{{ $sub->name }}</td>
                            <td><code>{{ $sub->slug }}</code></td>
                            <td>{{ $sub->order }}</td>
                            <td>
                                @if($sub->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('admin.subcategories.show', $sub->id) }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('admin.subcategories.edit', $sub->id) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No subcategories registered in this category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Meals inside Category -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Meals / Products ({{ $category->meals->count() }})</h3>
            <a href="{{ route('admin.meals.create', ['category_id' => $category->id]) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Add Meal
            </a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($category->meals as $meal)
                        <tr>
                            <td>
                                <img src="{{ $meal->image_url ?? 'https://via.placeholder.com/40' }}" alt="Meal" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover;">
                            </td>
                            <td>
                                <span style="font-weight: 600;">{{ $meal->title }}</span>
                            </td>
                            <td>{{ $meal->size ?? 'N/A' }}</td>
                            <td>
                                @if($meal->stock_quantity <= 0)
                                    <span class="badge badge-danger">Out of Stock</span>
                                @elseif($meal->stock_quantity <= 10)
                                    <span class="badge badge-warning">Low Stock ({{ $meal->stock_quantity }})</span>
                                @else
                                    <span class="badge badge-success">{{ $meal->stock_quantity }} units</span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">
                                @if($meal->discount_price)
                                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.8rem;">${{ number_format($meal->price, 2) }}</span>
                                    <span style="color: var(--color-success);">${{ number_format($meal->discount_price, 2) }}</span>
                                @else
                                    ${{ number_format($meal->price, 2) }}
                                @endif
                            </td>
                            <td>
                                @if($meal->is_available)
                                    <span class="badge badge-success">Available</span>
                                @else
                                    <span class="badge badge-danger">Unavailable</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('admin.meals.show', $meal->id) }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
                                    <a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No meals registered in this category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
