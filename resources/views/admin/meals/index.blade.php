@extends('components.admin.layout')

@section('title', 'Meals')
@section('breadcrumb_title', 'Meals')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <x-admin.page-header title="Meals Management">
        <x-slot name="action">
            <a href="{{ route('admin.meals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Meal
            </a>
        </x-slot>
    </x-admin.page-header>

    {{-- Filters --}}
    <x-admin.filter-form action="{{ route('admin.meals.index') }}" resetUrl="{{ route('admin.meals.index') }}">
        <x-admin.filter-input name="search" label="Search" width="3" lgWidth="4" 
            placeholder="Search by title, description, brand..." />
        
        <x-admin.filter-input name="category_id" label="Category" type="select" width="2" lgWidth="2">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" 
                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-admin.filter-input>
        
        <x-admin.filter-input name="stock_status" label="Stock" type="select" width="2" lgWidth="2">
            <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="availability" label="Status" type="select" width="2" lgWidth="2">
            <option value="1" {{ request('availability') == '1' ? 'selected' : '' }}>Available</option>
            <option value="0" {{ request('availability') == '0' ? 'selected' : '' }}>Unavailable</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="sort_by" label="Sort By" type="select" width="2" lgWidth="2">
            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Created Date</option>
            <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Title</option>
            <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
            <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
            <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Stock</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="sort_order" label="Order" type="select" width="1" lgWidth="1">
            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
        </x-admin.filter-input>
    </x-admin.filter-form>

    {{-- Table --}}
    @if($meals->count() > 0)
        <x-admin.table :pagination="$meals">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="w-auto">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col" class="text-end">Price</th>
                    <th scope="col" class="text-center">Stock</th>
                    <th scope="col">Rating</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meals as $meal)
                    <tr>
                        <td>
                            @if($meal->image_url)
                                <img src="{{ $meal->image_url }}" alt="{{ $meal->title }}" 
                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light text-center rounded" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $meal->title }}</strong>
                            @if($meal->offer_title)
                                <br><x-admin.badge variant="warning">{{ $meal->offer_title }}</x-admin.badge>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted d-block">{{ $meal->category?->name ?? '—' }}</small>
                            @if($meal->subcategory)
                                <small class="text-muted d-block">{{ $meal->subcategory->name }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>${{ number_format($meal->price, 2) }}</strong>
                            @if($meal->discount_price)
                                <br><del class="text-muted small">${{ number_format($meal->discount_price, 2) }}</del>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($meal->isInStock())
                                <x-admin.badge variant="success">{{ $meal->stock_quantity }}</x-admin.badge>
                            @else
                                <x-admin.badge variant="danger">0</x-admin.badge>
                            @endif
                        </td>
                        <td>
                            @if($meal->rating)
                                <span class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <strong>{{ $meal->rating }}</strong>
                                </span>
                                <small class="text-muted">({{ $meal->rating_count }})</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @if($meal->is_available)
                                <x-admin.badge variant="success">Available</x-admin.badge>
                            @else
                                <x-admin.badge variant="secondary">Unavailable</x-admin.badge>
                            @endif
                            @if($meal->is_featured)
                                <x-admin.badge variant="info">Featured</x-admin.badge>
                            @endif
                            @if($meal->is_hot)
                                <x-admin.badge variant="danger">Hot</x-admin.badge>
                            @endif
                        </td>
                        <td class="text-center">
                            <x-admin.actions 
                                id="{{ $meal->id }}"
                                show="{{ route('admin.meals.show', $meal) }}"
                                edit="{{ route('admin.meals.edit', $meal) }}"
                                delete="{{ route('admin.meals.destroy', $meal) }}" 
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-admin.card>
            <x-admin.empty-state 
                title="No meals found"
                description="Get started by creating your first meal."
                icon="bi-inbox"
            >
                <x-slot name="action">
                    <a href="{{ route('admin.meals.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Meal
                    </a>
                </x-slot>
            </x-admin.empty-state>
        </x-admin.card>
    @endif
</div>
@endsection

