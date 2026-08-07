@extends('layouts.admin')

@section('title', 'Order Details')
@section('breadcrumb', 'Orders / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Order {{ $order->order_number }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Orders
    </a>
</div>

@php
    $statusConfig = [
        'awaiting_payment' => ['class' => 'badge-secondary', 'label' => 'Awaiting Payment', 'icon' => 'fa-clock'],
        'placed'           => ['class' => 'badge-warning',   'label' => 'Placed',            'icon' => 'fa-file-circle-check'],
        'processing'       => ['class' => 'badge-info',      'label' => 'Processing',        'icon' => 'fa-gear'],
        'shipping'         => ['class' => 'badge-primary',   'label' => 'Shipping',          'icon' => 'fa-truck'],
        'out_for_delivery' => ['class' => 'badge-primary',   'label' => 'Out for Delivery',  'icon' => 'fa-map-location-dot'],
        'delivered'        => ['class' => 'badge-success',   'label' => 'Delivered',         'icon' => 'fa-circle-check'],
        'cancelled'        => ['class' => 'badge-danger',    'label' => 'Cancelled',         'icon' => 'fa-circle-xmark'],
    ];
    $cfg = $statusConfig[$order->status] ?? ['class' => 'badge-secondary', 'label' => ucfirst($order->status), 'icon' => 'fa-question'];
@endphp

<!-- Top Info Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="table-container" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600;">Order Status</div>
        <span class="badge {{ $cfg['class'] }}" style="font-size: 0.95rem; padding: 8px 18px;">
            <i class="fa-solid {{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
        </span>
    </div>
    <div class="table-container" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600;">Total Amount</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--color-success);">${{ number_format($order->total, 2) }}</div>
    </div>
    <div class="table-container" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600;">Payment Method</div>
        <div style="font-size: 1rem; font-weight: 700;">{{ ucfirst($order->payment_method ?? 'N/A') }}</div>
    </div>
    <div class="table-container" style="padding: 20px; text-align: center;">
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600;">Delivery Type</div>
        <div style="font-size: 1rem; font-weight: 700;">{{ ucfirst(str_replace('_', ' ', $order->delivery_type ?? 'N/A')) }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
    <!-- Left Column -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- Order Items -->
        <div class="table-container">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Order Items ({{ $order->items->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <img src="{{ $item->meal->image_url ?? 'https://placehold.co/44x44/eee/999?text=N/A' }}"
                                     alt="{{ $item->meal->title ?? 'Product' }}"
                                     style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">
                                <div>
                                    <span style="font-weight: 600;">{{ $item->meal->title ?? $item->meal_name ?? 'Deleted Product' }}</span>
                                    @if($item->size ?? $item->meal?->size)
                                        <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">{{ $item->size ?? $item->meal?->size }}</p>
                                    @endif
                                </div>
                            </td>
                            <td>${{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}</td>
                            <td style="font-weight: 700;">× {{ $item->quantity }}</td>
                            <td style="font-weight: 700; color: var(--color-primary);">
                                ${{ number_format(($item->unit_price ?? $item->price ?? 0) * $item->quantity, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Totals -->
            <div style="padding: 20px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; flex-direction: column; gap: 8px; max-width: 300px; margin-left: auto;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Subtotal:</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Discount:</span>
                        <span style="color: var(--color-success);">-${{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($order->shipping_fee > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Shipping Fee:</span>
                        <span>${{ number_format($order->shipping_fee, 2) }}</span>
                    </div>
                    @endif
                    @if($order->tax > 0)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Tax:</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; border-top: 2px solid var(--border-color); padding-top: 8px; font-size: 1.1rem; font-weight: 800;">
                        <span>Total:</span>
                        <span style="color: var(--color-success);">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Order Timeline</h3>
            @php
                $timeline = [
                    ['status' => 'placed',           'label' => 'Order Placed',        'time' => $order->placed_at,           'icon' => 'fa-file-circle-check'],
                    ['status' => 'processing',       'label' => 'Processing Started',  'time' => $order->processing_at,       'icon' => 'fa-gear'],
                    ['status' => 'shipping',         'label' => 'Shipped',             'time' => $order->shipping_at,         'icon' => 'fa-truck'],
                    ['status' => 'out_for_delivery', 'label' => 'Out for Delivery',    'time' => $order->out_for_delivery_at, 'icon' => 'fa-map-location-dot'],
                    ['status' => 'delivered',        'label' => 'Delivered',           'time' => $order->delivered_at,        'icon' => 'fa-circle-check'],
                    ['status' => 'cancelled',        'label' => 'Cancelled',           'time' => $order->cancelled_at,        'icon' => 'fa-circle-xmark'],
                ];
            @endphp
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($timeline as $step)
                    @if($step['time'])
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $step['status'] === 'cancelled' ? 'var(--color-danger)' : 'var(--color-success)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid {{ $step['icon'] }}" style="color: white; font-size: 0.85rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $step['label'] }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $step['time']->format('M d, Y \a\t H:i') }}</div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- Customer Info -->
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Customer</h3>
            @if($order->user)
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <img src="{{ $order->user->profile_image_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower($order->user->email)).'?d=mp' }}"
                     style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;" alt="Customer">
                <div>
                    <div style="font-weight: 700; color: var(--text-primary);">{{ $order->user->name }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $order->user->email }}</div>
                </div>
            </div>
            @if($order->user->phone)
                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-phone" style="margin-right: 6px;"></i>{{ $order->user->phone }}
                </div>
            @endif
            @else
                <p style="color: var(--text-muted);">Guest / Deleted user</p>
            @endif
        </div>

        <!-- Delivery Address -->
        @if($order->address)
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Delivery Address</h3>
            <address style="font-style: normal; line-height: 1.7; color: var(--text-secondary);">
                <strong>{{ $order->address->name ?? $order->user->name }}</strong><br>
                {{ $order->address->street ?? '' }}<br>
                {{ $order->address->city ?? '' }}@if($order->address->state), {{ $order->address->state }}@endif<br>
                {{ $order->address->country ?? '' }}
                @if($order->address->zip)
                    — {{ $order->address->zip }}
                @endif
            </address>
        </div>
        @endif

        <!-- Notes -->
        @if($order->notes)
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 10px; color: var(--text-primary);">Order Notes</h3>
            <p style="color: var(--text-secondary); font-style: italic;">{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Update Status -->
        @can('update', $order)
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Update Status</h3>
            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <select name="status" class="form-control" style="margin-bottom: 12px;">
                        @foreach(['placed' => 'Placed', 'processing' => 'Processing', 'shipping' => 'Shipping', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $val => $label)
                            <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;" onclick="return confirm('Update order status?')">
                    <i class="fa-solid fa-refresh"></i> Update Status
                </button>
            </form>
        </div>
        @endcan
    </div>
</div>
@endsection
