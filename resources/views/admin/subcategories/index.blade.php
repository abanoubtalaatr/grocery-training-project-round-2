@extends('layouts.admin')

@section('title', 'Subcategories')
@section('breadcrumb', 'Subcategories')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Subcategory Management</h1>
    @can('create', App\Models\Subcategory::class)
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add New Subcategory
        </a>
    @endcan
</div>

<!-- Filters & Search -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.subcategories.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search Subcategories</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name...">
        </div>
        <div class="form-group" style="width: 200px; margin-bottom: 0;">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">All Categories</option>
                @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="width: 160px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'category_id', 'status']))
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Subcategory Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Meals</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcategories as $subcategory)
                    <tr>
                        <td>
                            @if($subcategory->image_url)
                                <img src="{{ $subcategory->image_url }}" alt="Subcategory" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border-color);">
                            @else
                                <div style="width: 44px; height: 44px; background: var(--bg-secondary); border-radius: 8px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fa-solid fa-folder-tree" style="color: var(--text-muted);"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 600; color: var(--text-primary);">{{ $subcategory->name }}</span>
                            @if($subcategory->description)
                                <p style="font-size: 0.8rem; color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin: 0;">{{ $subcategory->description }}</p>
                            @endif
                        </td>
                        <td>
                            @if($subcategory->category)
                                <span class="badge badge-secondary">{{ $subcategory->category->name }}</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info" style="font-weight: 700;">{{ $subcategory->meals_count ?? $subcategory->meals()->count() }} meals</span>
                        </td>
                        <td style="font-weight: 600;">{{ $subcategory->order }}</td>
                        <td>
                            @if($subcategory->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                                <a href="{{ route('admin.subcategories.show', $subcategory->id) }}" class="btn btn-secondary btn-sm" title="View details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @can('update', $subcategory)
                                    <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endcan
                                @can('delete', $subcategory)
                                    <form action="{{ route('admin.subcategories.destroy', $subcategory->id) }}" method="POST" onsubmit="return confirm('Delete this subcategory?');" style="display: inline;">
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
                        <td colspan="7" class="empty-state">
                            <i class="fa-solid fa-folder-tree"></i>
                            <p>No subcategories found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subcategories->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $subcategories->links() }}
        </div>
    @endif
</div>
@endsection
