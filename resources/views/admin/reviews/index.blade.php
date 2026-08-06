@extends('admin.layouts.app')

@section('title', 'إدارة التقييمات')
@section('page-title', 'التقييمات')
@section('page-description', 'إدارة آراء وتقييمات العملاء')

@section('content')

{{-- Rating Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @for($i = 5; $i >= 1; $i--)
    @php $count = $ratingCounts[$i] ?? 0; @endphp
    <a href="{{ route('admin.reviews.index', ['rating' => $i]) }}"
       class="rounded-xl p-4 text-center transition-all hover:ring-1 hover:ring-amber-500/30 {{ request('rating') == $i ? 'ring-1 ring-amber-500/50' : '' }}"
       style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center justify-center gap-1 mb-2">
            @for($s = 1; $s <= 5; $s++)
            <svg class="w-3.5 h-3.5 {{ $s <= $i ? 'text-amber-400 fill-amber-400' : 'text-slate-600 fill-slate-600' }}" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            @endfor
        </div>
        <p class="text-xl font-bold text-white">{{ $count }}</p>
        <p class="text-[10px] text-slate-500 mt-0.5">{{ $i }} نجوم</p>
    </a>
    @endfor
    <a href="{{ route('admin.reviews.index') }}"
       class="rounded-xl p-4 text-center transition-all hover:ring-1 hover:ring-emerald-500/30 {{ !request('rating') ? 'ring-1 ring-emerald-500/50' : '' }}"
       style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
        </div>
        <p class="text-xl font-bold text-white">{{ array_sum($ratingCounts->toArray()) }}</p>
        <p class="text-[10px] text-slate-500 mt-0.5">الكل</p>
    </a>
</div>

{{-- Search --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex gap-3 flex-1">
        @if(request('rating'))
        <input type="hidden" name="rating" value="{{ request('rating') }}">
        @endif
        <div class="relative flex-1">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث في التقييمات..."
                   class="w-full bg-slate-900/60 border border-slate-700/50 text-slate-200 placeholder-slate-500 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-emerald-500/50">
        </div>
        <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-medium">بحث</button>
        @if(request()->hasAny(['search', 'rating']))
        <a href="{{ route('admin.reviews.index') }}"
           class="px-4 py-2.5 rounded-xl text-slate-400 text-sm border border-slate-700/50 hover:bg-slate-800/50 transition-colors">مسح</a>
        @endif
    </form>
</div>

{{-- Reviews Table --}}
<div class="rounded-2xl overflow-hidden"
     style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">

    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
        <div>
            <h3 class="text-sm font-bold text-white">قائمة التقييمات</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $reviews->total() }} تقييم</p>
        </div>
    </div>

    <div class="divide-y divide-slate-800/30">
        @forelse($reviews as $review)
        <div class="p-5 hover:bg-slate-800/10 transition-colors">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                {{-- User Avatar --}}
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                         style="background: linear-gradient(135deg, #059669, #0891b2);">
                        {{ mb_substr($review->user?->username ?? 'U', 0, 1) }}
                    </div>
                    <div class="sm:hidden">
                        <p class="text-sm font-medium text-white">{{ $review->user?->username ?? 'مجهول' }}</p>
                        <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                        <div>
                            <p class="text-sm font-medium text-white hidden sm:block">{{ $review->user?->username ?? 'مجهول' }}</p>
                            <p class="text-xs text-slate-400 hidden sm:block">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- Stars --}}
                            <div class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-4 h-4 {{ $s <= $review->rating ? 'text-amber-400 fill-amber-400' : 'text-slate-700 fill-slate-700' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            <span class="text-xs text-slate-500">{{ $review->rating }}.0</span>
                        </div>
                    </div>

                    {{-- Product --}}
                    @if($review->meal)
                    <div class="flex items-center gap-2 mb-2 p-2 rounded-lg bg-slate-800/40">
                        <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-xs text-slate-300 truncate">{{ $review->meal->title }}</span>
                    </div>
                    @endif

                    {{-- Comment --}}
                    @if($review->comment)
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $review->comment }}</p>
                    @else
                    <p class="text-xs text-slate-500 italic">لا يوجد تعليق</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="py-16 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center">
                    <svg class="w-7 h-7 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <p class="text-slate-400 text-sm">لا توجد تقييمات</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($reviews->hasPages())
    <div class="px-5 py-4 border-t border-slate-800/40 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            عرض {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} من {{ $reviews->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($reviews->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">السابق</span>
            @else
            <a href="{{ $reviews->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-slate-400 bg-slate-800/50 hover:bg-slate-700/50 transition-colors">السابق</a>
            @endif
            @if($reviews->hasMorePages())
            <a href="{{ $reviews->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-white btn-primary">التالي</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">التالي</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
