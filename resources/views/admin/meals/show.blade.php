@extends('components.admin.layout')

@section('title', $meal->title)
@section('breadcrumb_title', $meal->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">{{ $meal->title }}</h1>
        <div>
            <a href="{{ route('admin.meals.edit', $meal) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.meals.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('admin.partials.flash-messages')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            @if($meal->image_url)
                                <img src="{{ $meal->image_url }}" alt="{{ $meal->title }}" 
                                     class="img-fluid rounded" style="max-width: 100%;">
                            @else
                                <div class="bg-light text-center p-5 rounded" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                                    <div>
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No image available</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Product Information</h5>
                            <dl class="row">
                                <dt class="col-sm-5">Category:</dt>
                                <dd class="col-sm-7">{{ $meal->category?->name ?? 'N/A' }}</dd>

                                @if($meal->subcategory)
                                    <dt class="col-sm-5">Subcategory:</dt>
                                    <dd class="col-sm-7">{{ $meal->subcategory->name }}</dd>
                                @endif

                                @if($meal->brand)
                                    <dt class="col-sm-5">Brand:</dt>
                                    <dd class="col-sm-7">{{ $meal->brand }}</dd>
                                @endif

                                @if($meal->size)
                                    <dt class="col-sm-5">Size:</dt>
                                    <dd class="col-sm-7">{{ $meal->size }}</dd>
                                @endif

                                <dt class="col-sm-5">Slug:</dt>
                                <dd class="col-sm-7"><small class="text-muted">{{ $meal->slug }}</small></dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($meal->description)
                        <div class="mb-3">
                            <h6 class="card-subtitle mb-2 text-muted">Description</h6>
                            <p class="card-text">{{ $meal->description }}</p>
                        </div>
                    @endif

                    <!-- Features -->
                    @if($meal->features)
                        <div class="mb-3">
                            <h6 class="card-subtitle mb-2 text-muted">Features</h6>
                            <p class="card-text">{{ $meal->features }}</p>
                        </div>
                    @endif

                    <!-- Includes -->
                    @if($meal->includes)
                        <div class="mb-3">
                            <h6 class="card-subtitle mb-2 text-muted">Includes</h6>
                            <p class="card-text">{{ $meal->includes }}</p>
                        </div>
                    @endif

                    <!-- How to Use -->
                    @if($meal->how_to_use)
                        <div class="mb-3">
                            <h6 class="card-subtitle mb-2 text-muted">How to Use</h6>
                            <p class="card-text">{{ $meal->how_to_use }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Pricing</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-muted small">Regular Price</p>
                            <h3 class="text-success">${{ number_format($meal->price, 2) }}</h3>
                        </div>
                        @if($meal->discount_price)
                            <div class="col-md-4">
                                <p class="text-muted small">Discount Price</p>
                                <h3 class="text-danger">${{ number_format($meal->discount_price, 2) }}</h3>
                            </div>
                        @endif
                        @if($meal->offer_title)
                            <div class="col-md-4">
                                <p class="text-muted small">Offer</p>
                                <h3><span class="badge bg-warning text-dark">{{ $meal->offer_title }}</span></h3>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock & Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Inventory & Status</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small">Stock Quantity</p>
                            <h4>
                                @if($meal->isInStock())
                                    <span class="badge bg-success">{{ $meal->stock_quantity }} in stock</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small">Sold Count</p>
                            <h4>{{ $meal->sold_count }} sold</h4>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            @if($meal->is_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">Unavailable</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            @if($meal->is_featured)
                                <span class="badge bg-info">Featured</span>
                            @else
                                <span class="badge bg-light text-dark">Not Featured</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            @if($meal->is_hot)
                                <span class="badge bg-danger">Hot</span>
                            @else
                                <span class="badge bg-light text-dark">Not Hot</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Card -->
            @if($meal->rating)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Rating</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted small">Rating</p>
                                <div>
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <strong>{{ $meal->rating }}</strong> / 5.0
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small">Number of Reviews</p>
                                <h4>{{ $meal->rating_count }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dates Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Important Dates</h5>
                    <div class="row">
                        @if($meal->expiry_date)
                            <div class="col-md-6">
                                <p class="text-muted small">Expiry Date</p>
                                <p>
                                    <strong>{{ $meal->expiry_date->format('F d, Y') }}</strong>
                                    @if($meal->isExpired())
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @elseif($meal->daysUntilExpiry() !== null)
                                        <span class="badge bg-warning ms-2">{{ $meal->daysUntilExpiry() }} days left</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($meal->available_date)
                            <div class="col-md-6">
                                <p class="text-muted small">Available Date</p>
                                <p><strong>{{ $meal->available_date->format('F d, Y') }}</strong></p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Activity</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">{{ $meal->created_at->format('F d, Y \a\t H:i A') }}</dd>

                        <dt class="col-sm-4">Last Updated:</dt>
                        <dd class="col-sm-8">{{ $meal->updated_at->format('F d, Y \a\t H:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Reviews Card -->
            @if($meal->reviews->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Recent Reviews</h5>
                        <div class="review-list" style="max-height: 400px; overflow-y: auto;">
                            @foreach($meal->reviews->take(5) as $review)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <strong class="small">{{ $review->user?->firstname }} {{ $review->user?->lastname }}</strong>
                                        <small class="text-muted">{{ $review->created_at->format('M d') }}</small>
                                    </div>
                                    <div class="small text-warning mb-2">
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <i class="bi bi-star-fill"></i>
                                        @endfor
                                    </div>
                                    <p class="small mb-0">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Showing {{ min(5, $meal->reviews->count()) }} of {{ $meal->reviews->count() }} reviews</small>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center text-muted py-4">
                        <i class="bi bi-chat-left-text" style="font-size: 2rem;"></i>
                        <p class="mt-2">No reviews yet</p>
                    </div>
                </div>
            @endif

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.meals.edit', $meal) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Meal
                        </a>
                        <form method="POST" action="{{ route('admin.meals.destroy', $meal) }}" style="display: inline;"
                              onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash"></i> Delete Meal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
