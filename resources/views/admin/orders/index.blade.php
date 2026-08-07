@extends('layouts.admin')

@section('title', 'Orders')
@section('breadcrumb', 'Orders')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Order Management</h1>
    <span style="color: var(--text-muted); font-weight: 500;">Total: {{ $orders->total() }} orders</span>
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.orders.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Order # or customer name...">
        </div>
        <div class="form-group" style="width: 170px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="placed" {{ request('status') === 'placed' ? 'selected' : '' }}>Placed</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipping" {{ request('status') === 'shipping' ? 'selected' : '' }}>Shipping</option>
                <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="form-group" style="width: 160px; margin-bottom: 0;">
            <label for="payment_method" class="form-label">Payment</label>
            <select name="payment_method" id="payment_method" class="form-control">
                <option value="">All Methods</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="stripe" {{ request('payment_method') === 'stripe' ? 'selected' : '' }}>Stripe / Card</option>
            </select>
        </div>
        <div class="form-group" style="width: 150px; margin-bottom: 0;">
            <label for="date_from" class="form-label">From Date</label>
            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="form-group" style="width: 150px; margin-bottom: 0;">
            <label for="date_to" class="form-label">To Date</label>
            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight: 700; color: var(--color-primary); text-decoration: none;">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <span style="font-weight: 600;">{{ $order->user->name ?? 'Guest' }}</span>
                            @if($order->user)
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $order->user->email }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $order->items_count }} item(s)</span>
                        </td>
                        <td>
                            @if($order->payment_method === 'cash')
                                <span class="badge badge-warning"><i class="fa-solid fa-money-bill"></i> Cash</span>
                            @else
                                <span class="badge badge-info"><i class="fa-brands fa-stripe-s"></i> Card</span>
                            @endif
                        </td>
                        <td>
                            @if($order->delivery_type === 'pickup')
                                <span class="badge badge-secondary"><i class="fa-solid fa-store"></i> Pickup</span>
                            @else
                                <span class="badge badge-primary"><i class="fa-solid fa-truck"></i> Delivery</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: var(--color-success);">${{ number_format($order->total, 2) }}</td>
                        <td>
                            @php
                                $statusConfig = [
                                    'awaiting_payment' => ['class' => 'badge-secondary', 'label' => 'Awaiting Payment'],
                                    'placed'           => ['class' => 'badge-warning',   'label' => 'Placed'],
                                    'processing'       => ['class' => 'badge-info',      'label' => 'Processing'],
                                    'shipping'         => ['class' => 'badge-primary',   'label' => 'Shipping'],
                                    'out_for_delivery' => ['class' => 'badge-primary',   'label' => 'Out for Delivery'],
                                    'delivered'        => ['class' => 'badge-success',   'label' => 'Delivered'],
                                    'cancelled'        => ['class' => 'badge-danger',    'label' => 'Cancelled'],
                                ];
                                $cfg = $statusConfig[$order->status] ?? ['class' => 'badge-secondary', 'label' => ucfirst($order->status)];
                            @endphp
                            <span class="badge {{ $cfg['class'] }}">{{ $cfg['label'] }}</span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $order->created_at->format('M d, Y') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary btn-sm" title="View Order">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <p>No orders found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
