@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Dashboard Overview</h1>
    <span style="font-weight: 500; color: var(--text-muted);">{{ now()->format('l, F j, Y') }}</span>
</div>

<!-- Key Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Users</h3>
            <div class="stat-value">{{ number_format($totalUsers) }}</div>
        </div>
        <div class="stat-icon icon-blue">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Products</h3>
            <div class="stat-value">{{ number_format($totalMeals) }}</div>
        </div>
        <div class="stat-icon icon-cyan">
            <i class="fa-solid fa-bowl-food"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Orders</h3>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="stat-icon icon-orange">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Revenue</h3>
            <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="stat-icon icon-green">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
    </div>
</div>

<!-- Revenue and Stock Overview Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
    <!-- Revenue Breakdown Card -->
    <div class="table-container" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Revenue Breakdown</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--border-color);">
                <span style="color: var(--text-muted);">Today's Sales</span>
                <span style="font-weight: 700; color: var(--color-success);">${{ number_format($todayRevenue, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--border-color);">
                <span style="color: var(--text-muted);">This Month's Sales</span>
                <span style="font-weight: 700; color: var(--color-primary);">${{ number_format($monthlyRevenue, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                <span style="color: var(--text-muted);">Total Accumulated Sales</span>
                <span style="font-weight: 700; color: var(--text-primary);">${{ number_format($totalRevenue, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Stock and Risk Overview Card -->
    <div class="table-container" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Stock & Inventory Risks</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed var(--border-color);">
                <span style="color: var(--text-muted);">Low Stock Products (1-10 units)</span>
                <span class="badge {{ $lowStockProducts > 0 ? 'badge-warning' : 'badge-success' }}" style="font-size: 0.85rem; font-weight: 700;">
                    {{ $lowStockProducts }} items
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                <span style="color: var(--text-muted);">Out of Stock Products (0 units)</span>
                <span class="badge {{ $outOfStockProducts > 0 ? 'badge-danger' : 'badge-success' }}" style="font-size: 0.85rem; font-weight: 700;">
                    {{ $outOfStockProducts }} items
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Overview Row -->
<div class="table-container" style="padding: 20px; margin-bottom: 30px;">
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 15px;">Order Lifecycle Summary</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px;">
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Pending</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-warning);">{{ $pendingOrders }}</div>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Processing</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-info);">{{ $processingOrders }}</div>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Shipping</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-primary);">{{ $shippingOrders }}</div>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Delivered</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-success);">{{ $deliveredOrders }}</div>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <div style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 5px;">Cancelled</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--color-danger);">{{ $cancelledOrders }}</div>
        </div>
    </div>
</div>

<!-- Two-Column Layout for Tables -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px;">
    <!-- Recent Orders -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight: 600; color: var(--color-primary); text-decoration: none;">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>{{ $order->user ? $order->user->name : 'N/A' }}</td>
                            <td>
                                @if($order->status === 'placed')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($order->status === 'processing')
                                    <span class="badge badge-info">Processing</span>
                                @elseif($order->status === 'shipping' || $order->status === 'out_for_delivery')
                                    <span class="badge badge-primary">Shipping</span>
                                @elseif($order->status === 'delivered')
                                    <span class="badge badge-success">Delivered</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No orders recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="table-container">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Top Selling Products</h3>
            <a href="{{ route('admin.meals.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Price</th>
                        <th>Sold</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSellingMeals as $meal)
                        <tr>
                            <td style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $meal->image_url ?? 'https://via.placeholder.com/40' }}" alt="Meal" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.meals.show', $meal->id) }}" style="font-weight: 600; color: var(--text-primary); text-decoration: none;">
                                        {{ $meal->title }}
                                    </a>
                                </div>
                            </td>
                            <td>{{ $meal->size ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">${{ number_format($meal->final_price, 2) }}</td>
                            <td style="font-weight: 700; color: var(--color-primary);">{{ $meal->sold_count }} sold</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No products sold yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
