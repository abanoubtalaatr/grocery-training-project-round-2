@extends('layouts.admin')

@section('title', 'Edit Product')
@section('breadcrumb', 'Meals / Edit')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Edit Product: {{ $meal->title }}</h1>
    <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="form-card">
    <form action="{{ route('admin.meals.update', $meal->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="title" class="form-label">Product Title <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $meal->title) }}" required>
                @error('title') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category <span style="color: var(--color-danger)">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $meal->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="subcategory_id" class="form-label">Subcategory</label>
                <select name="subcategory_id" id="subcategory_id" class="form-control">
                    <option value="">-- None --</option>
                    @foreach($subcategories as $sub)
                        <option value="{{ $sub->id }}" {{ old('subcategory_id', $meal->subcategory_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
                @error('subcategory_id') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="price" class="form-label">Regular Price ($) <span style="color: var(--color-danger)">*</span></label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $meal->price) }}" required step="0.01" min="0">
                @error('price') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="discount_price" class="form-label">Discount Price ($)</label>
                <input type="number" name="discount_price" id="discount_price" class="form-control" value="{{ old('discount_price', $meal->getRawDiscountPrice()) }}" step="0.01" min="0">
                @error('discount_price') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="offer_title" class="form-label">Offer Title / Badge</label>
                <input type="text" name="offer_title" id="offer_title" class="form-control" value="{{ old('offer_title', $meal->offer_title) }}" placeholder="e.g. 20% OFF">
                @error('offer_title') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="brand" class="form-label">Brand</label>
                <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand', $meal->brand) }}">
                @error('brand') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="size" class="form-label">Size / Weight</label>
                <input type="text" name="size" id="size" class="form-control" value="{{ old('size', $meal->size) }}">
                @error('size') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="{{ old('stock_quantity', $meal->stock_quantity) }}" min="0">
                @error('stock_quantity') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date', $meal->expiry_date?->format('Y-m-d')) }}">
                @error('expiry_date') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="available_date" class="form-label">Available From</label>
                <input type="date" name="available_date" id="available_date" class="form-control" value="{{ old('available_date', $meal->available_date?->format('Y-m-d')) }}">
                @error('available_date') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $meal->description) }}</textarea>
                @error('description') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="features" class="form-label">Features</label>
                <textarea name="features" id="features" class="form-control" rows="3">{{ old('features', $meal->features) }}</textarea>
                @error('features') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="how_to_use" class="form-label">How to Use</label>
                <textarea name="how_to_use" id="how_to_use" class="form-control" rows="3">{{ old('how_to_use', $meal->how_to_use) }}</textarea>
                @error('how_to_use') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full">
                <label for="includes" class="form-label">Includes</label>
                <textarea name="includes" id="includes" class="form-control" rows="2">{{ old('includes', $meal->includes) }}</textarea>
                @error('includes') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Current Image -->
            @if($meal->image_url)
            <div class="form-group form-group-full">
                <label class="form-label">Current Image</label>
                <img src="{{ $meal->image_url }}" alt="Current" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
            </div>
            @endif

            <!-- Image Update -->
            <div class="form-group form-group-full" style="border: 1px dashed var(--border-color); padding: 15px; border-radius: 8px;">
                <label class="form-label" style="font-weight: 700;">Update Image (optional)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
                    <div style="border-right: 1px solid var(--border-color); padding-right: 20px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option A: Upload File</span>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                        @error('image_file') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option B: External URL</span>
                        <input type="url" name="image" id="image" class="form-control" value="{{ old('image') }}" placeholder="https://example.com/image.jpg">
                        @error('image') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Flags -->
            <div class="form-group form-group-full">
                <label class="form-label" style="font-weight: 700; margin-bottom: 10px; display: block;">Product Flags</label>
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $meal->is_available) ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-check-circle" style="color: var(--color-success);"></i> Available in Store
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $meal->is_featured) ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-star" style="color: var(--color-warning);"></i> Featured Product
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_hot" value="1" {{ old('is_hot', $meal->is_hot) ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-fire" style="color: var(--color-danger);"></i> Hot / Ready-to-eat
                    </label>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Product
            </button>
        </div>
    </form>
</div>
@endsection
