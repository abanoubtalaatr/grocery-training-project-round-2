@extends('components.admin.layout')

@section('title', 'Orders')
@section('breadcrumb_title', 'Orders')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Orders Management</h1>
    </div>

    <!-- Flash Messages -->
    @include('admin.partials.flash-messages')

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search order #, customer, email, phone..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_method" class="form-select">
                        <option value="">All Payment Methods</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="delivery_type" class="form-select">
                        <option value="">All Delivery Types</option>
                        @foreach($deliveryTypes as $type)
                            <option value="{{ $type }}" {{ request('delivery_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort_by" class="form-select">
                        <option value="placed_at" {{ request('sort_by', 'placed_at') == 'placed_at' ? 'selected' : '' }}>Order Date</option>
                        <option value="total" {{ request('sort_by') == 'total' ? 'selected' : '' }}>Total Amount</option>
                        <option value="customer" {{ request('sort_by') == 'customer' ? 'selected' : '' }}>Customer Name</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="sort_order" class="form-select">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Delivery</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $order->order_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $order->user->full_name ?? $order->user->username }}</strong>
                                    <br><small class="text-muted">{{ $order->user->email }}</small>
                                </div>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->delivery_type ?? 'N/A')) }}</small>
                            </td>
                            <td>
                                <small class="badge bg-light text-dark">{{ $order->items->count() }} items</small>
                            </td>
                            <td>
                                <strong>${{ number_format($order->total, 2) }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $order->placed_at?->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
