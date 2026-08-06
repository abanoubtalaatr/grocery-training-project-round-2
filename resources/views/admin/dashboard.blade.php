@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')
@section('page-description', 'نظرة شاملة على أداء المتجر اليوم')

@section('content')

{{-- ===== STATS CARDS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Revenue --}}
    <div class="stat-card col-span-2 lg:col-span-1 rounded-2xl p-5 relative overflow-hidden"
         style="background: linear-gradient(135deg, #064e3b, #065f46); border: 1px solid rgba(16,185,129,0.3);">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10"
             style="background: radial-gradient(circle, #10b981, transparent); transform: translate(30%, -30%);"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-emerald-300 font-medium mb-1">إجمالي الإيرادات</p>
                <p class="text-2xl font-bold text-white">${{ number_format($stats['total_revenue'], 0) }}</p>
                <p class="text-xs text-emerald-400/70 mt-1">{{ $ordersThisMonth }} طلب هذا الشهر</p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: rgba(16,185,129,0.2);">
                <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Orders --}}
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden"
         style="background: linear-gradient(135deg, #1e1b4b, #312e81); border: 1px solid rgba(99,102,241,0.3);">
        <div class="absolute top-0 right-0 w-28 h-28 rounded-full opacity-10"
             style="background: radial-gradient(circle, #6366f1, transparent); transform: translate(30%, -30%);"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-indigo-300 font-medium mb-1">الطلبات</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['total_orders']) }}</p>
                <p class="text-xs text-indigo-400/70 mt-1">
                    <span class="text-amber-400 font-semibold">{{ $stats['pending_orders'] }}</span> معلق
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: rgba(99,102,241,0.2);">
                <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Users --}}
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden"
         style="background: linear-gradient(135deg, #0c4a6e, #075985); border: 1px solid rgba(14,165,233,0.3);">
        <div class="absolute top-0 right-0 w-28 h-28 rounded-full opacity-10"
             style="background: radial-gradient(circle, #0ea5e9, transparent); transform: translate(30%, -30%);"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-sky-300 font-medium mb-1">المستخدمون</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
                <p class="text-xs text-sky-400/70 mt-1">
                    <span class="text-emerald-400 font-semibold">+{{ $newUsersThisMonth }}</span> هذا الشهر
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: rgba(14,165,233,0.2);">
                <svg class="w-6 h-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Meals --}}
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden"
         style="background: linear-gradient(135deg, #451a03, #78350f); border: 1px solid rgba(245,158,11,0.3);">
        <div class="absolute top-0 right-0 w-28 h-28 rounded-full opacity-10"
             style="background: radial-gradient(circle, #f59e0b, transparent); transform: translate(30%, -30%);"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-amber-300 font-medium mb-1">المنتجات</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['total_meals']) }}</p>
                <p class="text-xs text-amber-400/70 mt-1">{{ $stats['total_categories'] }} فئة</p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: rgba(245,158,11,0.2);">
                <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- ===== CHARTS ROW ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Revenue Chart --}}
    <div class="lg:col-span-2 rounded-2xl p-5"
         style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-white">الإيرادات الشهرية</h3>
                <p class="text-xs text-slate-400">آخر 6 أشهر</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="w-3 h-3 rounded-full" style="background: linear-gradient(135deg, #10b981, #06b6d4);"></span>
                    الإيراد
                </span>
            </div>
        </div>
        <div class="relative h-52">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Orders by Status --}}
    <div class="rounded-2xl p-5"
         style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="mb-5">
            <h3 class="text-sm font-bold text-white">حالة الطلبات</h3>
            <p class="text-xs text-slate-400">توزيع حسب الحالة</p>
        </div>
        <div class="relative h-44 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            @php
                $statusColors = [
                    'placed'          => '#10b981',
                    'processing'      => '#3b82f6',
                    'shipping'        => '#8b5cf6',
                    'out_for_delivery'=> '#f59e0b',
                    'delivered'       => '#06b6d4',
                    'cancelled'       => '#ef4444',
                    'awaiting_payment'=> '#6b7280',
                ];
                $statusLabels = [
                    'placed'          => 'مُقدَّم',
                    'processing'      => 'قيد المعالجة',
                    'shipping'        => 'شحن',
                    'out_for_delivery'=> 'في الطريق',
                    'delivered'       => 'مُسلَّم',
                    'cancelled'       => 'ملغي',
                    'awaiting_payment'=> 'بانتظار الدفع',
                ];
            @endphp
            @foreach(array_slice($ordersByStatus, 0, 4, true) as $status => $count)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0"
                          style="background: {{ $statusColors[$status] ?? '#6b7280' }};"></span>
                    <span class="text-xs text-slate-400">{{ $statusLabels[$status] ?? $status }}</span>
                </div>
                <span class="text-xs font-semibold text-white">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== BOTTOM ROW ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent Orders --}}
    <div class="lg:col-span-2 rounded-2xl overflow-hidden"
         style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
            <h3 class="text-sm font-bold text-white">آخر الطلبات</h3>
            <a href="{{ route('admin.orders.index') }}"
               class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
                عرض الكل ←
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-right border-b border-slate-800/40">
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400">رقم الطلب</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden sm:table-cell">العميل</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400">المبلغ</th>
                        <th class="px-5 py-3 text-xs font-semibold text-slate-400">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr class="border-b border-slate-800/30 hover:bg-slate-800/20">
                        <td class="px-5 py-3">
                            <span class="text-xs font-mono text-emerald-400">{{ $order->order_number }}</span>
                        </td>
                        <td class="px-5 py-3 hidden sm:table-cell">
                            <span class="text-xs text-slate-300">{{ $order->user?->username ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-semibold text-white">${{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badgeClasses = match($order->status) {
                                    'delivered'       => 'bg-emerald-400/15 text-emerald-400',
                                    'placed'          => 'bg-amber-400/15 text-amber-400',
                                    'processing'      => 'bg-blue-400/15 text-blue-400',
                                    'shipping'        => 'bg-purple-400/15 text-purple-400',
                                    'out_for_delivery'=> 'bg-cyan-400/15 text-cyan-400',
                                    'cancelled'       => 'bg-red-400/15 text-red-400',
                                    default           => 'bg-slate-400/15 text-slate-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClasses }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500 text-sm">لا توجد طلبات بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="rounded-2xl overflow-hidden"
         style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
            <h3 class="text-sm font-bold text-white">أفضل المنتجات</h3>
            <a href="{{ route('admin.meals.index') }}"
               class="text-xs text-emerald-400 hover:text-emerald-300 font-medium transition-colors">
                عرض الكل ←
            </a>
        </div>
        <div class="p-4 space-y-3">
            @forelse($topMeals as $index => $meal)
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-800/30 transition-colors">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"
                     style="background: {{ ['rgba(16,185,129,0.2)', 'rgba(99,102,241,0.2)', 'rgba(245,158,11,0.2)', 'rgba(14,165,233,0.2)', 'rgba(239,68,68,0.2)'][$index % 5] }}; color: {{ ['#10b981','#6366f1','#f59e0b','#0ea5e9','#ef4444'][$index % 5] }};">
                    {{ $index + 1 }}
                </div>
                @if($meal->image)
                <img src="{{ $meal->image_url }}" alt="{{ $meal->title }}"
                     class="w-9 h-9 rounded-lg object-cover flex-shrink-0"
                     onerror="this.style.display='none'">
                @else
                <div class="w-9 h-9 rounded-lg bg-slate-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-white truncate">{{ $meal->title }}</p>
                    <p class="text-[10px] text-slate-400">{{ $meal->sold_count ?? 0 }} مُباع</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs font-bold text-white">${{ number_format($meal->price, 0) }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-500 text-sm">لا توجد بيانات</div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ===== Revenue Chart =====
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');

    const monthlyData = @json($monthlyRevenue);
    const monthNames = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

    // Fill missing months with 0
    const last6 = [];
    for (let i = 5; i >= 0; i--) {
        const d = new Date();
        d.setMonth(d.getMonth() - i);
        const m = d.getMonth() + 1;
        const y = d.getFullYear();
        const found = monthlyData.find(r => parseInt(r.month) === m && parseInt(r.year) === y);
        last6.push({
            label: monthNames[m - 1],
            revenue: found ? parseFloat(found.revenue) : 0,
        });
    }

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: last6.map(d => d.label),
            datasets: [{
                label: 'الإيراد',
                data: last6.map(d => d.revenue),
                borderColor: '#10b981',
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return 'transparent';
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(16,185,129,0.3)');
                    gradient.addColorStop(1, 'rgba(16,185,129,0)');
                    return gradient;
                },
                borderWidth: 2.5,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#0f172a',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.95)',
                    titleColor: '#94a3b8',
                    bodyColor: '#10b981',
                    borderColor: 'rgba(16,185,129,0.3)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: ctx => ' $' + ctx.parsed.y.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ','),
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#64748b', font: { size: 11, family: 'Cairo' } },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: 'Cairo' },
                        callback: v => '$' + v.toFixed(0),
                    },
                    beginAtZero: true,
                }
            }
        }
    });

    // ===== Status Donut Chart =====
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($ordersByStatus);
    const statusLabelsMap = {
        placed: 'مُقدَّم', processing: 'معالجة', shipping: 'شحن',
        out_for_delivery: 'في الطريق', delivered: 'مُسلَّم',
        cancelled: 'ملغي', awaiting_payment: 'بانتظار الدفع',
    };
    const statusColorsMap = {
        placed: '#10b981', processing: '#3b82f6', shipping: '#8b5cf6',
        out_for_delivery: '#f59e0b', delivered: '#06b6d4',
        cancelled: '#ef4444', awaiting_payment: '#6b7280',
    };

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(k => statusLabelsMap[k] || k),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.keys(statusData).map(k => statusColorsMap[k] || '#475569'),
                borderColor: 'rgba(15,23,42,0.8)',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.95)',
                    titleColor: '#94a3b8',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                }
            }
        }
    });
});
</script>
@endpush
