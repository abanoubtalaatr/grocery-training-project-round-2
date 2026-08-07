@extends('layouts.admin')

@section('title', 'View Subcategory')
@section('breadcrumb', 'Subcategories / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">{{ $subcategory->name }}</h1>
    <div style="display: flex; gap: 10px;">
        @can('update', $subcategory)
            <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
        @endcan
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    <!-- Image Card -->
    <div class="table-container" style="padding: 25px; display: flex; flex-direction: column; align-items: center; gap: 15px;">
        @if($subcategory->image_url)
            <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name }}" style="width: 100%; max-width: 200px; height: 200px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color);">
        @else
            <div style="width: 200px; height: 200px; background: var(--bg-secondary); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                <i class="fa-solid fa-folder-tree" style="font-size: 3rem; color: var(--text-muted);"></i>
            </div>
        @endif
        <span class="badge {{ $subcategory->is_active ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.9rem; padding: 6px 16px;">
            {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <!-- Details Card -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Subcategory Details</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Name:</span>
                <span style="color: var(--text-primary); font-weight: 600;">{{ $subcategory->name }}</span>
            </div>
            <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Parent Category:</span>
                <span>{{ $subcategory->category->name ?? '—' }}</span>
            </div>
            <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Slug:</span>
                <code>{{ $subcategory->slug }}</code>
            </div>
            <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Sort Order:</span>
                <span>{{ $subcategory->order }}</span>
            </div>
            <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Total Meals:</span>
                <span class="badge badge-info">{{ $subcategory->meals()->count() }} meals</span>
            </div>
            @if($subcategory->description)
            <div style="display: flex; gap: 15px;">
                <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">Description:</span>
                <p style="color: var(--text-secondary); margin: 0;">{{ $subcategory->description }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Related Meals -->
@if($subcategory->meals()->count() > 0)
<div class="table-container" style="margin-top: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Meals in This Subcategory</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subcategory->meals()->take(10)->get() as $meal)
                <tr>
                    <td>
                        <img src="{{ $meal->image_url ?? 'https://via.placeholder.com/40' }}" alt="{{ $meal->title }}" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;">
                    </td>
                    <td style="font-weight: 600;">{{ $meal->title }}</td>
                    <td>${{ number_format($meal->final_price, 2) }}</td>
                    <td>{{ $meal->stock_quantity }}</td>
                    <td>
                        <span class="badge {{ $meal->is_available ? 'badge-success' : 'badge-danger' }}">
                            {{ $meal->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
