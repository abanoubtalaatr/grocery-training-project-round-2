@extends('layouts.admin')

@section('title', 'Add Product')
@section('breadcrumb', 'Meals / Add New')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Add New Product</h1>
    <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="form-card">
    <form action="{{ route('admin.meals.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
            <!-- Title -->
            <div class="form-group form-group-full">
                <label for="title" class="form-label">Product Title <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Fresh Organic Apples 1kg">
                @error('title') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category_id" class="form-label">Category <span style="color: var(--color-danger)">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Subcategory -->
            <div class="form-group">
                <label for="subcategory_id" class="form-label">Subcategory</label>
                <select name="subcategory_id" id="subcategory_id" class="form-control">
                    <option value="">-- Select Subcategory --</option>
                    @foreach($subcategories as $sub)
                        <option value="{{ $sub->id }}" {{ old('subcategory_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
                @error('subcategory_id') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Price -->
            <div class="form-group">
                <label for="price" class="form-label">Regular Price ($) <span style="color: var(--color-danger)">*</span></label>
                <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" required step="0.01" min="0" placeholder="0.00">
                @error('price') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Discount Price -->
            <div class="form-group">
                <label for="discount_price" class="form-label">Discount Price ($)</label>
                <input type="number" name="discount_price" id="discount_price" class="form-control" value="{{ old('discount_price') }}" step="0.01" min="0" placeholder="Optional discount price">
                @error('discount_price') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Offer Title -->
            <div class="form-group">
                <label for="offer_title" class="form-label">Offer Title / Badge</label>
                <input type="text" name="offer_title" id="offer_title" class="form-control" value="{{ old('offer_title') }}" placeholder="e.g. 20% OFF, Sale">
                @error('offer_title') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Brand -->
            <div class="form-group">
                <label for="brand" class="form-label">Brand</label>
                <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand') }}" placeholder="e.g. Organic Farm">
                @error('brand') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Size -->
            <div class="form-group">
                <label for="size" class="form-label">Size / Weight</label>
                <input type="text" name="size" id="size" class="form-control" value="{{ old('size') }}" placeholder="e.g. 1kg, 500ml">
                @error('size') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Stock Quantity -->
            <div class="form-group">
                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" min="0">
                @error('stock_quantity') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Expiry Date -->
            <div class="form-group">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                @error('expiry_date') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Available Date -->
            <div class="form-group">
                <label for="available_date" class="form-label">Available From</label>
                <input type="date" name="available_date" id="available_date" class="form-control" value="{{ old('available_date') }}">
                @error('available_date') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Description -->
            <div class="form-group form-group-full">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Product description...">{{ old('description') }}</textarea>
                @error('description') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Features -->
            <div class="form-group">
                <label for="features" class="form-label">Features</label>
                <textarea name="features" id="features" class="form-control" rows="3" placeholder="Key product features...">{{ old('features') }}</textarea>
                @error('features') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- How to Use -->
            <div class="form-group">
                <label for="how_to_use" class="form-label">How to Use / Instructions</label>
                <textarea name="how_to_use" id="how_to_use" class="form-control" rows="3" placeholder="Usage instructions...">{{ old('how_to_use') }}</textarea>
                @error('how_to_use') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Includes -->
            <div class="form-group form-group-full">
                <label for="includes" class="form-label">Includes / Contents</label>
                <textarea name="includes" id="includes" class="form-control" rows="2" placeholder="What's included in the package...">{{ old('includes') }}</textarea>
                @error('includes') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <!-- Image Upload -->
            <div class="form-group form-group-full" style="border: 1px dashed var(--border-color); padding: 15px; border-radius: 8px;">
                <label class="form-label" style="font-weight: 700;">Product Image</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
                    <div style="border-right: 1px solid var(--border-color); padding-right: 20px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option A: Upload File</span>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*" onchange="previewImage(this)">
                        <div id="image-preview" style="margin-top: 10px; display: none;">
                            <img id="preview-img" src="" alt="Preview" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                        </div>
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
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-check-circle" style="color: var(--color-success);"></i> Available in Store
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-star" style="color: var(--color-warning);"></i> Featured Product
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                        <input type="checkbox" name="is_hot" value="1" {{ old('is_hot') ? 'checked' : '' }} style="width: auto;">
                        <i class="fa-solid fa-fire" style="color: var(--color-danger);"></i> Hot / Ready-to-eat
                    </label>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save Product
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('preview-img');
    const container = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-load subcategories when category changes
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const subSelect = document.getElementById('subcategory_id');
    subSelect.innerHTML = '<option value="">-- Loading... --</option>';
    if (!categoryId) {
        subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
        return;
    }
    fetch(`/admin-dashboard/subcategories-by-category/${categoryId}`)
        .then(r => r.json())
        .then(data => {
            subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
            data.forEach(sub => {
                subSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
            });
        })
        .catch(() => {
            subSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
        });
});
</script>
@endsection
