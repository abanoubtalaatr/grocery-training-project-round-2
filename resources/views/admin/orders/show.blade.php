@extends('components.admin.layout')

@section('title', $order->order_number)
@section('breadcrumb_title', $order->order_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Order {{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Flash Messages -->
    @include('admin.partials.flash-messages')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Status Update Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Status</h5>
                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="d-flex gap-2 align-items-end">
                        @csrf
                        @method('PATCH')
                        <div class="flex-grow-1">
                            <label for="status" class="form-label mb-2">Update Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="awaiting_payment" {{ $order->status == 'awaiting_payment' ? 'selected' : '' }}>Awaiting Payment</option>
                                <option value="placed" {{ $order->status == 'placed' ? 'selected' : '' }}>Placed</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipping" {{ $order->status == 'shipping' ? 'selected' : '' }}>Shipping</option>
                                <option value="out_for_delivery" {{ $order->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update
                        </button>
                    </form>

                    <!-- Status Timeline -->
                    <div class="mt-4">
                        <h6 class="mb-3">Status Timeline</h6>
                        <div class="timeline">
                            @if($order->placed_at)
                                <div class="timeline-item mb-3">
                                    <small class="text-muted">Placed: {{ $order->placed_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                            @if($order->processing_at)
                                <div class="timeline-item mb-3">
                                    <small class="text-muted">Processing: {{ $order->processing_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                            @if($order->shipping_at)
                                <div class="timeline-item mb-3">
                                    <small class="text-muted">Shipping: {{ $order->shipping_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                            @if($order->out_for_delivery_at)
                                <div class="timeline-item mb-3">
                                    <small class="text-muted">Out for Delivery: {{ $order->out_for_delivery_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                            @if($order->delivered_at)
                                <div class="timeline-item">
                                    <small class="text-muted">Delivered: {{ $order->delivered_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                            @if($order->cancelled_at)
                                <div class="timeline-item">
                                    <small class="text-danger">Cancelled: {{ $order->cancelled_at->format('F d, Y H:i A') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Customer Information</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8">{{ $order->user->full_name ?? $order->user->username }}</dd>

                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                                </dd>

                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">{{ $order->user->phone ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Username:</dt>
                                <dd class="col-sm-8">{{ $order->user->username }}</dd>
                            </dl>
                            <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-sm btn-outline-primary">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Delivery Address</h6>
                            @if($order->address)
                                <dl class="row">
                                    <dt class="col-sm-4">Address:</dt>
                                    <dd class="col-sm-8">{{ $order->address->address_line }}</dd>

                                    <dt class="col-sm-4">City:</dt>
                                    <dd class="col-sm-8">{{ $order->address->city }}</dd>

                                    <dt class="col-sm-4">Postal:</dt>
                                    <dd class="col-sm-8">{{ $order->address->postal_code }}</dd>

                                    <dt class="col-sm-4">Country:</dt>
                                    <dd class="col-sm-8">{{ $order->address->country ?? 'N/A' }}</dd>
                                </dl>
                            @else
                                <p class="text-muted">No delivery address</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Meal</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->meal->title ?? 'Product Removed' }}</strong>
                                            @if($item->meal)
                                                <br><small class="text-muted">{{ $item->meal->category?->name }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">
                                            @if($item->discount_amount)
                                                -${{ number_format($item->discount_amount, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end"><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Summary</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Subtotal:</dt>
                                <dd class="col-sm-7">${{ number_format($order->subtotal, 2) }}</dd>

                                <dt class="col-sm-5">Tax:</dt>
                                <dd class="col-sm-7">${{ number_format($order->tax, 2) }}</dd>

                                <dt class="col-sm-5">Shipping:</dt>
                                <dd class="col-sm-7">${{ number_format($order->shipping_fee, 2) }}</dd>

                                <dt class="col-sm-5">Discount:</dt>
                                <dd class="col-sm-7">-${{ number_format($order->discount, 2) }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Amount:</strong>
                                    <h4 class="mb-0 text-primary">${{ number_format($order->total, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($order->invoice)
                        <div class="mt-3">
                            <a href="#" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-file-pdf"></i> View Invoice
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Notes</h5>

                    <!-- Add Note Form -->
                    <form method="POST" action="{{ route('admin.orders.addNote', $order) }}" class="mb-4">
                        @csrf
                        <div class="input-group">
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                    placeholder="Add a note..." rows="2"></textarea>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Add Note
                            </button>
                        </div>
                        @error('notes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </form>

                    <!-- Notes List -->
                    @if(optional($order->notes)->count())
                        <div class="notes-list">
                            @foreach($order->notes as $note)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            @if($note->specialNote)
                                                <span class="badge bg-info">{{ $note->specialNote->name }}</span>
                                            @endif
                                            <small class="text-muted">{{ $note->created_at?->format('F d, Y H:i A') }}</small>
                                        </div>
                                        <form method="POST" action="{{ route('admin.orders.deleteNote', [$order, $note]) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Delete this note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <p class="mb-0">{{ $note->notes }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No notes yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Info</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            @switch($order->status)
                                @case('awaiting_payment')
                                    <span class="badge bg-warning text-dark">Awaiting Payment</span>
                                    @break
                                @case('placed')
                                    <span class="badge bg-info">Placed</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-primary">Processing</span>
                                    @break
                                @case('shipping')
                                    <span class="badge bg-primary">Shipping</span>
                                    @break
                                @case('out_for_delivery')
                                    <span class="badge bg-info">Out for Delivery</span>
                                    @break
                                @case('delivered')
                                    <span class="badge bg-success">Delivered</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-6">Payment:</dt>
                        <dd class="col-sm-6">
                            <small>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</small>
                        </dd>

                        <dt class="col-sm-6">Delivery:</dt>
                        <dd class="col-sm-6">
                            <small>{{ ucfirst(str_replace('_', ' ', $order->delivery_type ?? 'N/A')) }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Activity Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Activity</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Created:</dt>
                        <dd class="col-sm-6">
                            <small class="text-muted">{{ $order->created_at?->format('M d, Y H:i A') }}</small>
                        </dd>

                        <dt class="col-sm-6">Updated:</dt>
                        <dd class="col-sm-6">
                            <small class="text-muted">{{ $order->updated_at?->format('M d, Y H:i A') }}</small>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
