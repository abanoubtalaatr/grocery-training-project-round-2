@extends('layouts.admin')

@section('title', 'Add FAQ')
@section('breadcrumb', 'FAQs / Add New')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Add New FAQ</h1>
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <form action="{{ route('admin.faqs.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="question" class="form-label">Question <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="question" id="question" class="form-control" value="{{ old('question') }}" required placeholder="e.g. How do I track my order?">
                @error('question') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full">
                <label for="answer" class="form-label">Answer <span style="color: var(--color-danger)">*</span></label>
                <textarea name="answer" id="answer" class="form-control" rows="6" required placeholder="Detailed answer...">{{ old('answer') }}</textarea>
                @error('answer') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-control" value="{{ old('category') }}" placeholder="e.g. Orders, Payments, Delivery">
                @error('category') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="order" class="form-label">Display Order</label>
                <input type="number" name="order" id="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                @error('order') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" name="is_active" id="is_active" style="width: auto;" checked value="1">
                <label for="is_active" class="form-label" style="margin-bottom: 0; font-weight: 700; cursor: pointer;">Active (Visible to users)</label>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save FAQ</button>
        </div>
    </form>
</div>
@endsection
