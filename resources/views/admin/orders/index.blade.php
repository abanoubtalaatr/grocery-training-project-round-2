@extends('admin.layouts.app')

@section('title', 'إدارة الطلبات')
@section('page-title', 'الطلبات')
@section('page-description', 'متابعة وإدارة طلبات العملاء')

@section('content')

@php
$statusConfig = [
    'placed'           => ['label' => 'مُقدَّم',        'class' => 'bg-amber-400/15 text-amber-400'],
    'processing'       => ['label' => 'قيد المعالجة',   'class' => 'bg-blue-400/15 text-blue-400'],
    'shipping'         => ['label' => 'شحن',            'class' => 'bg-purple-400/15 text-purple-400'],
    'out_for_delivery' => ['label' => 'في الطريق',      'class' => 'bg-cyan-400/15 text-cyan-400'],
    'delivered'        => ['label' => 'مُسلَّم',         'class' => 'bg-emerald-400/15 text-emerald-400'],
    'cancelled'        => ['label' => 'ملغي',           'class' => 'bg-red-400/15 text-red-400'],
    'awaiting_payment' => ['label' => 'بانتظار الدفع',  'class' => 'bg-slate-400/15 text-slate-400'],
];
@endphp

{{-- Status Quick Filters --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.orders.index') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ !request('status') ? 'text-white' : 'text-slate-400 bg-slate-800/50 hover:bg-slate-800' }}"
       style="{{ !request('status') ? 'background: linear-gradient(135deg, #059669, #0891b2);' : '' }}">
        الكل <span class="mr-1 opacity-70">{{ $orders->total() }}</span>
    </a>
    @foreach($statusConfig as $key => $cfg)
    @php $count = $statusCounts[$key] ?? 0; @endphp
    @if($count > 0)
    <a href="{{ route('admin.orders.index', ['status' => $key]) }}"
       class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all {{ request('status') === $key ? $cfg['class'] . ' ring-1 ring-current' : 'text-slate-400 bg-slate-800/50 hover:bg-slate-800' }}">
        {{ $cfg['label'] }} <span class="mr-1 opacity-70">{{ $count }}</span>
    </a>
    @endif
    @endforeach
</div>

{{-- Search Bar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-3 flex-1">
        @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="relative flex-1">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث برقم الطلب أو اسم العميل..."
                   class="w-full bg-slate-900/60 border border-slate-700/50 text-slate-200 placeholder-slate-500 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-emerald-500/50">
        </div>
        <button type="submit"
                class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-medium">
            بحث
        </button>
        @if(request()->hasAny(['search', 'status', 'payment']))
        <a href="{{ route('admin.orders.index') }}"
           class="px-4 py-2.5 rounded-xl text-slate-400 text-sm border border-slate-700/50 hover:bg-slate-800/50 transition-colors">
            مسح
        </a>
        @endif
    </form>
</div>

{{-- Orders Table --}}
<div class="rounded-2xl overflow-hidden"
     style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">

    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
        <div>
            <h3 class="text-sm font-bold text-white">قائمة الطلبات</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $orders->total() }} طلب</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-right border-b border-slate-800/40">
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">رقم الطلب</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden sm:table-cell">العميل</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">المبلغ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">الحالة</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden md:table-cell">طريقة الدفع</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden lg:table-cell">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/30">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-5 py-4">
                        <span class="text-sm font-mono font-semibold text-emerald-400">
                            {{ $order->order_number }}
                        </span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                 style="background: linear-gradient(135deg, #059669, #0891b2);">
                                {{ mb_substr($order->user?->username ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-sm text-slate-300">{{ $order->user?->username ?? 'مجهول' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div>
                            <span class="text-sm font-bold text-white">${{ number_format($order->total, 2) }}</span>
                            @if($order->discount > 0)
                            <p class="text-[10px] text-emerald-400">خصم: ${{ number_format($order->discount, 2) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php $cfg = $statusConfig[$order->status] ?? ['label' => $order->status, 'class' => 'bg-slate-400/15 text-slate-400']; @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cfg['class'] }}">
                            {{ $cfg['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-xs text-slate-400 capitalize">
                            {{ str_replace('_', ' ', $order->payment_method ?? '—') }}
                        </span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-xs text-slate-400">{{ $order->created_at->format('d M Y') }}</span>
                        <p class="text-[10px] text-slate-600">{{ $order->created_at->format('h:i A') }}</p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-slate-400 text-sm">لا توجد طلبات</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-800/40 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            عرض {{ $orders->firstItem() }}–{{ $orders->lastItem() }} من {{ $orders->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($orders->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">السابق</span>
            @else
            <a href="{{ $orders->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-slate-400 bg-slate-800/50 hover:bg-slate-700/50 transition-colors">السابق</a>
            @endif
            @if($orders->hasMorePages())
            <a href="{{ $orders->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-white btn-primary">التالي</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">التالي</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
