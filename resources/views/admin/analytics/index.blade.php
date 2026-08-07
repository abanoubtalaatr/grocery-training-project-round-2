@extends('layouts.admin')

@section('title', 'Analytics')
@section('breadcrumb', 'Analytics')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Analytics & Reports</h1>
    <span style="color: var(--text-muted); font-weight: 500;">Last 12 months overview</span>
</div>

<!-- Revenue Chart & Orders by Status -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px;">
    <!-- Revenue Chart -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Monthly Revenue (Delivered Orders)</h3>
        <canvas id="revenueChart" height="120"></canvas>
    </div>

    <!-- Orders by Status Pie -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Orders by Status</h3>
        <canvas id="statusChart" height="220"></canvas>
    </div>
</div>

<!-- Payment & Delivery + New Users -->
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 25px; margin-bottom: 25px;">
    <!-- Payment Breakdown -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Payment Methods</h3>
        @forelse($paymentBreakdown as $pm)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border-color);">
            <span style="font-weight: 600; text-transform: capitalize;">{{ $pm->payment_method ?? 'N/A' }}</span>
            <div style="text-align: right;">
                <div style="font-weight: 700; color: var(--color-primary);">{{ $pm->count }} orders</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">${{ number_format($pm->revenue, 2) }}</div>
            </div>
        </div>
        @empty
            <p style="color: var(--text-muted);">No data yet.</p>
        @endforelse
    </div>

    <!-- Delivery Breakdown -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Delivery Type</h3>
        @forelse($deliveryBreakdown as $dt)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border-color);">
            <span style="font-weight: 600; text-transform: capitalize;">{{ str_replace('_', ' ', $dt->delivery_type ?? 'N/A') }}</span>
            <span class="badge badge-info">{{ $dt->count }} orders</span>
        </div>
        @empty
            <p style="color: var(--text-muted);">No data yet.</p>
        @endforelse
    </div>

    <!-- New Users Chart -->
    <div class="table-container" style="padding: 25px;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">New Users (6 Months)</h3>
        <canvas id="usersChart" height="180"></canvas>
    </div>
</div>

<!-- Top Products -->
<div class="table-container" style="margin-bottom: 25px;">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Top 10 Best-Selling Products</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Units Sold</th>
                    <th>Favorites</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $product)
                <tr>
                    <td style="font-weight: 700; color: var(--text-muted);">{{ $loop->iteration }}</td>
                    <td style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $product->image_url ?? 'https://placehold.co/36x36/eee/999?text=N/A' }}" alt="{{ $product->title }}" style="width: 36px; height: 36px; border-radius: 6px; object-fit: cover;">
                        <span style="font-weight: 600;">{{ $product->title }}</span>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $product->category->name ?? '—' }}</td>
                    <td style="font-weight: 700; color: var(--color-primary);">${{ number_format($product->final_price, 2) }}</td>
                    <td style="font-weight: 700; color: var(--color-success);">{{ number_format($product->sold_count) }}</td>
                    <td><span class="badge badge-info">{{ $product->favorites_count }}</span></td>
                    <td>
                        @if($product->stock_quantity <= 0)
                            <span class="badge badge-danger">Out</span>
                        @elseif($product->stock_quantity <= 10)
                            <span class="badge badge-warning">{{ $product->stock_quantity }}</span>
                        @else
                            <span style="color: var(--color-success); font-weight: 600;">{{ $product->stock_quantity }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="fa-solid fa-chart-bar"></i>
                        <p>No sales data yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Category Performance -->
<div class="table-container">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Category Performance</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Products</th>
                    <th>Total Units Sold</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                @php $maxSold = $categoryStats->max('meals_sum_sold_count') ?: 1; @endphp
                @forelse($categoryStats as $cat)
                <tr>
                    <td style="font-weight: 600;">{{ $cat->name }}</td>
                    <td><span class="badge badge-secondary">{{ $cat->meals_count }}</span></td>
                    <td style="font-weight: 700;">{{ number_format($cat->meals_sum_sold_count ?? 0) }}</td>
                    <td style="width: 200px;">
                        <div style="background: var(--bg-secondary); border-radius: 999px; height: 8px; overflow: hidden;">
                            <div style="background: var(--color-primary); height: 100%; width: {{ ($cat->meals_sum_sold_count / $maxSold) * 100 }}%; border-radius: 999px; transition: width 0.6s ease;"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="fa-solid fa-tags"></i>
                        <p>No categories found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartColors = {
    primary: '#3b82f6', success: '#10b981', warning: '#f59e0b',
    danger: '#ef4444', info: '#06b6d4', purple: '#8b5cf6',
    muted: '#6b7280',
};

// Revenue Chart
const revenueData = @json($revenueByMonth);
const revenueLabels = Object.keys(revenueData);
const revenueValues = Object.values(revenueData).map(v => parseFloat(v));

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue ($)',
            data: revenueValues,
            borderColor: chartColors.primary,
            backgroundColor: 'rgba(59,130,246,0.08)',
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: chartColors.primary,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } }
        }
    }
});

// Orders by Status Pie
const statusData = @json($ordersByStatus);
const statusLabels = { placed:'Placed',processing:'Processing',shipping:'Shipping',out_for_delivery:'Out for Delivery',delivered:'Delivered',cancelled:'Cancelled',awaiting_payment:'Awaiting Payment' };
const statusColors = [chartColors.warning,chartColors.info,chartColors.primary,chartColors.purple,chartColors.success,chartColors.danger,chartColors.muted];
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData).map(k => statusLabels[k] || k),
        datasets: [{ data: Object.values(statusData), backgroundColor: statusColors, borderWidth: 2 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } } }
});

// New Users Chart
const usersData = @json($newUsersByMonth);
new Chart(document.getElementById('usersChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(usersData),
        datasets: [{
            label: 'New Users',
            data: Object.values(usersData),
            backgroundColor: chartColors.success,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endsection
