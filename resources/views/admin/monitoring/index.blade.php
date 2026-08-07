@extends('layouts.admin')

@section('title', 'System Risks')
@section('breadcrumb', 'System Risks & Monitoring')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">System Risks & Monitoring</h1>
    <span style="color: var(--text-muted); font-weight: 500;">Real-time risk assessment</span>
</div>

<!-- Risk Summary Cards -->
<div class="stats-grid" style="margin-bottom: 25px;">
    <div class="stat-card" style="border-left: 4px solid var(--color-danger);">
        <div class="stat-info">
            <h3>Out of Stock</h3>
            <div class="stat-value" style="color: var(--color-danger);">{{ $summaryStats['out_of_stock'] }}</div>
        </div>
        <div class="stat-icon icon-red"><i class="fa-solid fa-box-open"></i></div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--color-warning);">
        <div class="stat-info">
            <h3>Low Stock (≤10)</h3>
            <div class="stat-value" style="color: var(--color-warning);">{{ $summaryStats['low_stock'] }}</div>
        </div>
        <div class="stat-icon icon-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #f97316;">
        <div class="stat-info">
            <h3>Expiring in 30 Days</h3>
            <div class="stat-value" style="color: #f97316;">{{ $summaryStats['expiring_soon'] }}</div>
        </div>
        <div class="stat-icon icon-orange"><i class="fa-solid fa-calendar-xmark"></i></div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--color-danger);">
        <div class="stat-info">
            <h3>Expired Products</h3>
            <div class="stat-value" style="color: var(--color-danger);">{{ $summaryStats['expired'] }}</div>
        </div>
        <div class="stat-icon icon-red"><i class="fa-solid fa-skull-crossbones"></i></div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--color-warning);">
        <div class="stat-info">
            <h3>Stalled Orders</h3>
            <div class="stat-value" style="color: var(--color-warning);">{{ $summaryStats['stalled_orders'] }}</div>
        </div>
        <div class="stat-icon icon-orange"><i class="fa-solid fa-hourglass-half"></i></div>
    </div>
    <div class="stat-card" style="border-left: 4px solid #6b7280;">
        <div class="stat-info">
            <h3>Inactive Users</h3>
            <div class="stat-value" style="color: var(--text-muted);">{{ $summaryStats['inactive_users'] }}</div>
        </div>
        <div class="stat-icon" style="background: rgba(107,114,128,0.15);"><i class="fa-solid fa-user-slash" style="color: var(--text-muted);"></i></div>
    </div>
</div>

<!-- Out of Stock -->
@if($outOfStock->count() > 0)
<div class="table-container" style="margin-bottom: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-box-open" style="color: var(--color-danger); font-size: 1.2rem;"></i>
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-danger);">Out of Stock Products ({{ $outOfStock->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Price</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($outOfStock as $meal)
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <img src="{{ $meal->image_url ?? 'https://placehold.co/36x36/eee/999?text=N/A' }}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                        <span style="font-weight:600;">{{ $meal->title }}</span>
                    </td>
                    <td>{{ $meal->category->name ?? '—' }}</td>
                    <td style="font-weight:700;">${{ number_format($meal->final_price, 2) }}</td>
                    <td><a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-primary btn-sm">Restock</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Low Stock -->
@if($lowStock->count() > 0)
<div class="table-container" style="margin-bottom: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-triangle-exclamation" style="color: var(--color-warning); font-size: 1.2rem;"></i>
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-warning);">Low Stock Products — 1 to 10 units ({{ $lowStock->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Stock Left</th><th>Price</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($lowStock as $meal)
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <img src="{{ $meal->image_url ?? 'https://placehold.co/36x36/eee/999?text=N/A' }}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                        <span style="font-weight:600;">{{ $meal->title }}</span>
                    </td>
                    <td>{{ $meal->category->name ?? '—' }}</td>
                    <td><span class="badge badge-warning" style="font-size:0.9rem;">{{ $meal->stock_quantity }} left</span></td>
                    <td>${{ number_format($meal->final_price, 2) }}</td>
                    <td><a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-warning btn-sm">Update Stock</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Expiring Soon -->
@if($expiringSoon->count() > 0)
<div class="table-container" style="margin-bottom: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-calendar-xmark" style="color: #f97316; font-size: 1.2rem;"></i>
        <h3 style="font-size: 1rem; font-weight: 700; color: #f97316;">Expiring Within 30 Days ({{ $expiringSoon->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Expiry Date</th><th>Days Left</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($expiringSoon as $meal)
                @php $daysLeft = now()->diffInDays($meal->expiry_date, false); @endphp
                <tr>
                    <td style="font-weight:600;">{{ $meal->title }}</td>
                    <td>{{ $meal->category->name ?? '—' }}</td>
                    <td>{{ $meal->expiry_date->format('M d, Y') }}</td>
                    <td>
                        <span class="badge {{ $daysLeft <= 7 ? 'badge-danger' : 'badge-warning' }}">
                            {{ $daysLeft }} day(s)
                        </span>
                    </td>
                    <td><a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-secondary btn-sm">Review</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Expired Products -->
@if($expiredMeals->count() > 0)
<div class="table-container" style="margin-bottom: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-skull-crossbones" style="color: var(--color-danger); font-size: 1.2rem;"></i>
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-danger);">Expired Products ({{ $expiredMeals->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Expired On</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($expiredMeals as $meal)
                <tr>
                    <td style="font-weight:600; color: var(--color-danger);">{{ $meal->title }}</td>
                    <td>{{ $meal->category->name ?? '—' }}</td>
                    <td style="color: var(--color-danger); font-weight: 600;">{{ $meal->expiry_date->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex;gap:8px;">
                            <a href="{{ route('admin.meals.edit', $meal->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.meals.destroy', $meal->id) }}" method="POST" onsubmit="return confirm('Delete expired product?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Stalled Orders -->
@if($stalledOrders->count() > 0)
<div class="table-container">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-hourglass-half" style="color: var(--color-warning); font-size: 1.2rem;"></i>
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-warning);">Stalled Orders — Placed &gt; 24h ago ({{ $stalledOrders->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Placed At</th><th>Action</th></tr>
            </thead>
            <tbody>
                @foreach($stalledOrders as $order)
                <tr>
                    <td style="font-weight:700; color: var(--color-primary);">{{ $order->order_number }}</td>
                    <td>{{ $order->user->name ?? 'Guest' }}</td>
                    <td style="font-weight:700;">${{ number_format($order->total, 2) }}</td>
                    <td style="color: var(--color-warning); font-weight:600;">{{ ($order->placed_at ?? $order->created_at)->format('M d, Y H:i') }}</td>
                    <td><a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-primary btn-sm">Process Now</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($summaryStats['out_of_stock'] === 0 && $summaryStats['low_stock'] === 0 && $summaryStats['expired'] === 0 && $summaryStats['stalled_orders'] === 0)
<div class="table-container" style="padding: 50px; text-align: center;">
    <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: var(--color-success); margin-bottom: 15px;"></i>
    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary);">All systems normal!</h2>
    <p style="color: var(--text-muted);">No critical risks detected at this time.</p>
</div>
@endif

@endsection
