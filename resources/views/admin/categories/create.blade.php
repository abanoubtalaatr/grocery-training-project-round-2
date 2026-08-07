@extends('layouts.admin')

@section('title', 'Add Category')
@section('breadcrumb', 'Categories / Add New')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Add New Category</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="form-card">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
            <!-- Category Name -->
            <div class="form-group">
                <label for="name" class="form-label">Category Name <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Vegetables & Fruits">
                @error('name')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Sort Order -->
            <div class="form-group">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0" placeholder="e.g. 0, 1, 2">
                @error('sort_order')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group form-group-full">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Brief description of the category...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="form-feedback-invalid">{{ $message }}</span>
                @enderror
            </div>

            <!-- Image Upload Selection -->
            <div class="form-group form-group-full" style="border: 1px dashed var(--border-color); padding: 15px; border-radius: 8px;">
                <label class="form-label" style="font-weight: 700;">Category Image (Provide one of the following)</label>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
                    <!-- Option A: Upload File -->
                    <div style="border-right: 1px solid var(--border-color); padding-right: 20px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option A: Upload File</span>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                        @error('image_file')
                            <span class="form-feedback-invalid">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Option B: External URL -->
                    <div>
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 5px;">Option B: External URL</span>
                        <input type="url" name="image_url" id="image_url" class="form-control" value="{{ old('image_url') }}" placeholder="https://example.com/image.jpg">
                        @error('image_url')
                            <span class="form-feedback-invalid">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status Checkbox -->
            <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" name="is_active" id="is_active" style="width: auto;" checked value="1">
                <label for="is_active" class="form-label" style="margin-bottom: 0; cursor: pointer; font-weight: 700;">Set as Active (Visible in Store)</label>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save Category
            </button>
        </div>
    </form>
</div>
@endsection
