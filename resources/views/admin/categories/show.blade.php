@php($pageTitle = $category->name)

<x-admin.layout>
    <div class="mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-chevron-left"></i> Back to Categories
        </a>
        <div class="d-flex justify-content-between align-items-start mt-2">
            <div>
                <h1 class="h3 mb-1">{{ $category->name }}</h1>
                <p class="text-muted mb-0">{{ $category->slug }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-dark rounded-pill btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" 
                    class="d-inline" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill btn-sm">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('admin.partials.flash-messages')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Details</h2>

                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted small">Status</div>
                        <div class="col-sm-9">
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted small">Created</div>
                        <div class="col-sm-9">{{ $category->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted small">Updated</div>
                        <div class="col-sm-9">{{ $category->updated_at->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3 text-muted small">Sort Order</div>
                        <div class="col-sm-9">{{ $category->sort_order }}</div>
                    </div>
                </div>
            </div>

            @if($category->description)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">Description</h2>
                        <p class="mb-0">{{ $category->description }}</p>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">
                        <i class="bi bi-cup-straw me-1"></i> Meals in this Category
                    </h2>
                    @if($category->meals->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="border-bottom">
                                    <tr class="text-muted small">
                                        <th class="px-0 py-2">Name</th>
                                        <th class="px-0 py-2">Price</th>
                                        <th class="px-0 py-2">Stock</th>
                                        <th class="px-0 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->meals as $meal)
                                        <tr class="border-bottom">
                                            <td class="px-0 py-2">{{ $meal->title }}</td>
                                            <td class="px-0 py-2">${{ number_format($meal->price, 2) }}</td>
                                            <td class="px-0 py-2">{{ $meal->stock_quantity }}</td>
                                            <td class="px-0 py-2">
                                                <span class="badge {{ $meal->is_available ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                                    {{ $meal->is_available ? 'Available' : 'Unavailable' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No meals in this category yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($category->image)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" 
                            class="img-fluid rounded-3 mb-3">
                        <p class="small text-muted mb-0">Category Image</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
