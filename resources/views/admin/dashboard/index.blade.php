@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Page Title -->
    <div>
        <h2 class="text-2xl font-bold text-white">Dashboard Overview</h2>
        <p class="text-gray-400 mt-1">Welcome back, {{ Auth::user()->firstname }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Users -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Users</p>
                    <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['users']['total']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-users text-blue-400 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-400 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> {{ $stats['users']['growth_percentage'] }}%
                </span>
                <span class="text-gray-500 ml-2">this month</span>
            </div>
        </div>

        <!-- Orders -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Orders</p>
                    <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['orders']['total']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-400 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-400 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> {{ $stats['orders']['growth_percentage'] }}%
                </span>
                <span class="text-gray-500 ml-2">this month</span>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-white mt-1">${{ number_format($stats['revenue']['total'], 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-400 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-400 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> {{ $stats['revenue']['growth_percentage'] }}%
                </span>
                <span class="text-gray-500 ml-2">this month</span>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Products</p>
                    <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['products']['total']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-orange-500/20 flex items-center justify-center">
                    <i class="fas fa-box text-orange-400 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400">{{ $stats['products']['active'] }} active</span>
                <span class="text-red-400 ml-2">{{ $stats['products']['out_of_stock'] }} out of stock</span>
            </div>
        </div>
    </div>

    <!-- Chart + Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-gray-800 rounded-xl p-6 border border-gray-700">
            <h3 class="text-lg font-semibold text-white mb-4">Revenue Overview</h3>
            <canvas id="revenueChart" height="300"></canvas>
        </div>

        <!-- Pending Items -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <h3 class="text-lg font-semibold text-white mb-4">Pending Items</h3>
            <div class="space-y-4">
                <a href="{{ route('admin.reviews.index') }}?is_approved=0" 
                   class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                            <i class="fas fa-star text-yellow-400"></i>
                        </div>
                        <span class="text-gray-300">Pending Reviews</span>
                    </div>
                    <span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded text-sm">
                        {{ $stats['pending_items']['reviews'] }}
                    </span>
                </a>

                <a href="{{ route('admin.contacts.index') }}?status=new" 
                   class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-envelope text-blue-400"></i>
                        </div>
                        <span class="text-gray-300">New Messages</span>
                    </div>
                    <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-sm">
                        {{ $stats['pending_items']['messages'] }}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold text-white mb-4">Recent Activity</h3>
        <div class="space-y-3">
            @foreach($recentActivity as $activity)
                <div class="flex items-center justify-between p-3 bg-gray-700/30 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center">
                            <i class="fas {{ $activity['type'] === 'order' ? 'fa-shopping-bag' : 'fa-user' }} text-gray-300"></i>
                        </div>
                        <div>
                            <p class="text-white text-sm">
                                @if($activity['type'] === 'order')
                                    New order #{{ $activity['order_number'] }} by {{ $activity['user'] }}
                                @else
                                    New user {{ $activity['name'] }} registered
                                @endif
                            </p>
                            <p class="text-gray-500 text-xs">{{ $activity['created_at'] }}</p>
                        </div>
                    </div>
                    @if($activity['type'] === 'order')
                        <span class="text-green-400 text-sm">${{ number_format($activity['total'], 2) }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.label),
            datasets: [{
                label: 'Revenue ($)',
                data: chartData.map(item => item.revenue),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#3b82f6',
                pointHoverRadius: 6,
            }, {
                label: 'Orders',
                data: chartData.map(item => item.orders),
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#8b5cf6',
                pointHoverRadius: 6,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#9ca3af',
                        usePointStyle: true,
                        padding: 20,
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(75, 85, 99, 0.3)' },
                    ticks: { color: '#9ca3af' }
                },
                y: {
                    grid: { color: 'rgba(75, 85, 99, 0.3)' },
                    ticks: { 
                        color: '#9ca3af',
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#9ca3af' }
                }
            }
        }
    });
</script>
@endpush
