@extends('layouts.admin')

@section('title', 'View Product')
@section('breadcrumb', 'Meals / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">{{ $meal->title }}</h1>
    <div style="display: flex; gap: 10px;">
        @can('update', $meal)
            <a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
        @endcan
        <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 25px;">
    <!-- Image & Status Card -->
    <div class="table-container" style="padding: 25px; display: flex; flex-direction: column; align-items: center; gap: 15px;">
        <img src="{{ $meal->image_url ?? 'https://placehold.co/250x250/eee/999?text=No+Image' }}"
             alt="{{ $meal->title }}"
             style="width: 100%; height: 250px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color);">

        <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;">
            @if($meal->is_available)
                <span class="badge badge-success">Available</span>
            @else
                <span class="badge badge-secondary">Unavailable</span>
            @endif
            @if($meal->is_featured)
                <span class="badge badge-info">Featured</span>
            @endif
            @if($meal->is_hot)
                <span class="badge badge-warning">Hot</span>
            @endif
            @if($meal->hasOffer())
                <span class="badge badge-danger">On Sale</span>
            @endif
        </div>

        <div style="width: 100%; border-top: 1px solid var(--border-color); padding-top: 15px;">
            <div style="text-align: center; margin-bottom: 10px;">
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--color-primary);">${{ number_format($meal->final_price, 2) }}</span>
                @if($meal->getRawDiscountPrice())
                    <span style="font-size: 1rem; color: var(--text-muted); text-decoration: line-through; margin-left: 8px;">${{ number_format($meal->price, 2) }}</span>
                @endif
            </div>
            @if($meal->offer_title)
                <p style="text-align: center; font-weight: 600; color: var(--color-danger);">{{ $meal->offer_title }}</p>
            @endif
        </div>

        <div style="width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; text-align: center;">
            <div style="background: var(--bg-secondary); border-radius: 8px; padding: 10px;">
                <div style="font-size: 1.3rem; font-weight: 700; color: var(--text-primary);">{{ $meal->stock_quantity ?? 0 }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">In Stock</div>
            </div>
            <div style="background: var(--bg-secondary); border-radius: 8px; padding: 10px;">
                <div style="font-size: 1.3rem; font-weight: 700; color: var(--color-primary);">{{ $meal->sold_count ?? 0 }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Sold</div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Core Info -->
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Product Information</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach([
                    ['Category', $meal->category->name ?? '—'],
                    ['Subcategory', $meal->subcategory->name ?? '—'],
                    ['Brand', $meal->brand ?? '—'],
                    ['Size / Weight', $meal->size ?? '—'],
                    ['Rating', ($meal->rating ?? 0) . ' / 5.0 (' . ($meal->rating_count ?? 0) . ' reviews)'],
                    ['Expiry Date', $meal->expiry_date ? $meal->expiry_date->format('M d, Y') : '—'],
                    ['Available From', $meal->available_date ? $meal->available_date->format('M d, Y') : '—'],
                    ['Created', $meal->created_at->format('M d, Y H:i')],
                ] as [$label, $value])
                <div style="display: flex; gap: 15px; border-bottom: 1px dashed var(--border-color); padding-bottom: 10px;">
                    <span style="min-width: 140px; font-weight: 600; color: var(--text-muted);">{{ $label }}:</span>
                    <span>{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Description -->
        @if($meal->description)
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">Description</h3>
            <p style="color: var(--text-secondary); line-height: 1.6; white-space: pre-wrap;">{{ $meal->description }}</p>
        </div>
        @endif

        <!-- Features & Instructions -->
        @if($meal->features || $meal->how_to_use || $meal->includes)
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Additional Details</h3>
            @if($meal->features)
                <div style="margin-bottom: 15px;">
                    <span style="font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">Features:</span>
                    <p style="color: var(--text-secondary); white-space: pre-wrap;">{{ $meal->features }}</p>
                </div>
            @endif
            @if($meal->how_to_use)
                <div style="margin-bottom: 15px;">
                    <span style="font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">How to Use:</span>
                    <p style="color: var(--text-secondary); white-space: pre-wrap;">{{ $meal->how_to_use }}</p>
                </div>
            @endif
            @if($meal->includes)
                <div>
                    <span style="font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">Includes:</span>
                    <p style="color: var(--text-secondary); white-space: pre-wrap;">{{ $meal->includes }}</p>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Reviews Section -->
@if($meal->reviews && $meal->reviews->count() > 0)
<div class="table-container" style="margin-top: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Customer Reviews ({{ $meal->reviews->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meal->reviews->take(10) as $review)
                <tr>
                    <td style="font-weight: 600;">{{ $review->user->name ?? 'Anonymous' }}</td>
                    <td>
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star" style="color: {{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }}; font-size: 0.8rem;"></i>
                        @endfor
                    </td>
                    <td style="max-width: 400px;">{{ $review->comment ?? '—' }}</td>
                    <td>{{ $review->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
