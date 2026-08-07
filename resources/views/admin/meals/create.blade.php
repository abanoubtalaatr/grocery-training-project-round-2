@extends('components.admin.layout')

@section('title', 'Create Meal')
@section('breadcrumb_title', 'Create Meal')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="mb-0">Create New Meal</h1>
            </div>

            <!-- Flash Messages -->
            @include('admin.partials.flash-messages')

            <!-- Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.meals.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category and Subcategory -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subcategory_id" class="form-label">Subcategory</label>
                                <select class="form-select @error('subcategory_id') is-invalid @enderror" 
                                        id="subcategory_id" name="subcategory_id">
                                    <option value="">Select Subcategory (optional)</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subcategory_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price Section -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="discount_price" class="form-label">Discount Price</label>
                                <input type="number" step="0.01" class="form-control @error('discount_price') is-invalid @enderror" 
                                       id="discount_price" name="discount_price" value="{{ old('discount_price') }}">
                                @error('discount_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="offer_title" class="form-label">Offer Title</label>
                                <input type="text" class="form-control @error('offer_title') is-invalid @enderror" 
                                       id="offer_title" name="offer_title" placeholder="e.g. 20% OFF" value="{{ old('offer_title') }}">
                                @error('offer_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock and Quantity -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                       id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
                                @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sold_count" class="form-label">Sold Count</label>
                                <input type="number" class="form-control @error('sold_count') is-invalid @enderror" 
                                       id="sold_count" name="sold_count" value="{{ old('sold_count', 0) }}">
                                @error('sold_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="size" class="form-label">Size</label>
                                <input type="text" class="form-control @error('size') is-invalid @enderror" 
                                       id="size" name="size" placeholder="e.g. 500g" value="{{ old('size') }}">
                                @error('size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rating Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="rating" class="form-label">Rating</label>
                                <input type="number" step="0.1" min="0" max="5" class="form-control @error('rating') is-invalid @enderror" 
                                       id="rating" name="rating" value="{{ old('rating') }}">
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="rating_count" class="form-label">Rating Count</label>
                                <input type="number" class="form-control @error('rating_count') is-invalid @enderror" 
                                       id="rating_count" name="rating_count" value="{{ old('rating_count', 0) }}">
                                @error('rating_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Brand and Features -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                       id="brand" name="brand" value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="features" class="form-label">Features</label>
                                <input type="text" class="form-control @error('features') is-invalid @enderror" 
                                       id="features" name="features" placeholder="e.g. Organic, Vegan" value="{{ old('features') }}">
                                @error('features')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="mb-3">
                            <label for="includes" class="form-label">Includes</label>
                            <textarea class="form-control @error('includes') is-invalid @enderror" 
                                      id="includes" name="includes" rows="2">{{ old('includes') }}</textarea>
                            @error('includes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="how_to_use" class="form-label">How to Use</label>
                            <textarea class="form-control @error('how_to_use') is-invalid @enderror" 
                                      id="how_to_use" name="how_to_use" rows="2">{{ old('how_to_use') }}</textarea>
                            @error('how_to_use')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="available_date" class="form-label">Available Date</label>
                                <input type="date" class="form-control @error('available_date') is-invalid @enderror" 
                                       id="available_date" name="available_date" value="{{ old('available_date') }}">
                                @error('available_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status Toggles -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" 
                                       {{ old('is_available') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_available">
                                    Available
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Featured
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_hot" name="is_hot" value="1" 
                                       {{ old('is_hot') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_hot">
                                    Hot (Ready to Eat)
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.meals.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Create Meal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update subcategories when category changes
    document.getElementById('category_id').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategorySelect = document.getElementById('subcategory_id');
        
        if (categoryId) {
            fetch(`/admin/categories/${categoryId}/subcategories`)
                .then(res => res.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">Select Subcategory (optional)</option>';
                    data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.text = sub.name;
                        subcategorySelect.appendChild(option);
                    });
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory (optional)</option>';
        }
    });
</script>
@endsection
