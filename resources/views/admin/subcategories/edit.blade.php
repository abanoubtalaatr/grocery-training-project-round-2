@extends('layouts.admin')

@section('title', 'Edit Subcategory')
@section('breadcrumb', 'Subcategories / Edit')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Edit Subcategory: {{ $subcategory->name }}</h1>
    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="form-card">
    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <!-- Parent Category -->
            <div class="form-group">
                <label for="category_id" class="form-label">Parent Category <span style="color: var(--color-danger)">*</span></label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Sort Order -->
            <div class="form-group">
                <label for="order" class="form-label">Sort Order</label>
                <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $subcategory->order) }}" min="0">
                @error('order')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Subcategory Name -->
            <div class="form-group form-group-full">
                <label for="name" class="form-label">Subcategory Name <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $subcategory->name) }}" required>
                @error('name')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group form-group-full">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $subcategory->description) }}</textarea>
                @error('description')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Current Image -->
            @if($subcategory->image_url)
            <div class="form-group form-group-full">
                <label class="form-label">Current Image</label>
                <img src="{{ $subcategory->image_url }}" alt="Current" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
            </div>
            @endif

            <!-- Image Upload -->
            <div class="form-group form-group-full" style="border: 1px dashed var(--border-color); padding: 15px; border-radius: 8px;">
                <label class="form-label" style="font-weight: 700;">Update Image (optional)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
                    <div style="border-right: 1px solid var(--border-color); padding-right: 20px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option A: Upload File</span>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                        @error('image_file')
                            <span class="form-feedback-invalid">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option B: External URL</span>
                        <input type="url" name="image_url" id="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://example.com/image.jpg">
                        @error('image_url')
                            <span class="form-feedback-invalid">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" name="is_active" id="is_active" style="width: auto;" value="1" {{ old('is_active', $subcategory->is_active) ? 'checked' : '' }}>
                <label for="is_active" class="form-label" style="margin-bottom: 0; cursor: pointer; font-weight: 700;">Active (Visible in Store)</label>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Subcategory
            </button>
        </div>
    </form>
</div>
@endsection
