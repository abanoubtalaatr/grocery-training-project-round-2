@php($pageTitle = 'Categories')

<x-admin.layout>
    {{-- Page Header --}}
    <x-admin.page-header title="Categories" subtitle="Manage product categories and organize your catalog.">
        <x-slot name="action">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Category
            </a>
        </x-slot>
    </x-admin.page-header>

    {{-- Filters --}}
    <x-admin.filter-form action="{{ route('admin.categories.index') }}" resetUrl="{{ route('admin.categories.index') }}">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" 
                   placeholder="Search by name or description..." 
                   value="{{ $search ?? '' }}"
                   aria-label="Search categories">
        </div>
        
        <div class="col-md-3">
            <select name="sort" class="form-select form-select-sm" aria-label="Sort by">
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Name</option>
                <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Date</option>
                <option value="is_active" {{ $sort === 'is_active' ? 'selected' : '' }}>Status</option>
            </select>
        </div>
        
        <div class="col-md-3">
            <select name="direction" class="form-select form-select-sm" aria-label="Sort direction">
                <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>Ascending</option>
                <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>Descending</option>
            </select>
        </div>
    </x-admin.filter-form>

    {{-- Table --}}
    @if($categories->count() > 0)
        <x-admin.table :pagination="$categories">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="w-auto">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col" class="text-center">Meals</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col">Created</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                                 class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $category->name }}</div>
                            <small class="text-muted d-block">{{ $category->slug }}</small>
                        </td>
                        <td class="text-center">
                            <x-admin.badge variant="secondary">{{ $category->meals_count ?? 0 }}</x-admin.badge>
                        </td>
                        <td class="text-center">
                            <x-admin.badge variant="{{ $category->is_active ? 'success' : 'secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </x-admin.badge>
                        </td>
                        <td>
                            <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-center">
                            <x-admin.actions 
                                id="{{ $category->id }}"
                                show="{{ route('admin.categories.show', $category) }}"
                                edit="{{ route('admin.categories.edit', $category) }}"
                                delete="{{ route('admin.categories.destroy', $category) }}" 
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-admin.card>
            <x-admin.empty-state 
                title="No categories found"
                description="Get started by creating your first category."
                icon="bi-list-check"
            >
                <x-slot name="action">
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Category
                    </a>
                </x-slot>
            </x-admin.empty-state>
        </x-admin.card>
    @endif
</x-admin.layout>

