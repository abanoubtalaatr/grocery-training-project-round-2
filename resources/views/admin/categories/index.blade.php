@extends('layouts.admin')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Category Management</h1>
    @can('create', App\Models\Category::class)
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add New Category
        </a>
    @endcan
</div>

<!-- Filters & Search -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.categories.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
            <label for="search" class="form-label">Search Categories</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name or description...">
        </div>
        <div class="form-group" style="width: 180px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Category Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Subcategories</th>
                    <th>Meals</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <img src="{{ $category->image_url }}" alt="Category" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color);">
                        </td>
                        <td>
                            <span style="font-weight: 600; color: var(--text-primary);">{{ $category->name }}</span>
                            @if($category->description)
                                <p style="font-size: 0.8rem; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $category->description }}
                                </p>
                            @endif
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>
                            <span class="badge badge-secondary" style="font-weight: 700;">
                                {{ $category->subcategories_count }} subcategories
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info" style="font-weight: 700;">
                                {{ $category->meals_count }} meals
                            </span>
                        </td>
                        <td style="font-weight: 600;">{{ $category->sort_order }}</td>
                        <td>
                            @if($category->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                                <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-secondary btn-sm" title="View details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @can('update', $category)
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endcan
                                @can('delete', $category)
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category? (Soft Deletion)');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-tags"></i>
                            <p>No categories found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
