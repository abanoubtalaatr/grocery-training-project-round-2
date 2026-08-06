@extends('admin.layouts.app')

@section('title', 'إدارة الفئات')
@section('page-title', 'الفئات')
@section('page-description', 'إدارة فئات المنتجات')

@section('content')

{{-- Search --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex gap-3 flex-1">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث باسم الفئة..."
                   class="w-full bg-slate-900/60 border border-slate-700/50 text-slate-200 placeholder-slate-500 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-emerald-500/50">
        </div>
        <select name="status" class="bg-slate-900/60 border border-slate-700/50 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500/50 min-w-[140px]" onchange="this.form.submit()">
            <option value="">كل الحالات</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>نشطة</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشطة</option>
        </select>
        <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-medium">تصفية</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.categories.index') }}"
           class="px-4 py-2.5 rounded-xl text-slate-400 text-sm border border-slate-700/50 hover:bg-slate-800/50 transition-colors">مسح</a>
        @endif
    </form>
</div>

{{-- Categories Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
    @forelse($categories as $category)
    <div class="rounded-2xl overflow-hidden hover:ring-1 hover:ring-emerald-500/30 transition-all group"
         style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">

        {{-- Image --}}
        <div class="relative h-36 bg-slate-800/50 overflow-hidden">
            @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 onerror="this.style.display='none'">
            @else
            <div class="w-full h-full flex items-center justify-center"
                 style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(6,182,212,0.1));">
                <svg class="w-12 h-12 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            @endif
            {{-- Status Badge --}}
            <div class="absolute top-2 left-2">
                @if($category->is_active)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/90 text-white">نشطة</span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/80 text-white">غير نشطة</span>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h4 class="text-sm font-bold text-white">{{ $category->name }}</h4>
                    @if($category->description)
                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $category->description }}</p>
                    @endif
                </div>
                <span class="text-xs text-slate-500 bg-slate-800/60 px-2 py-1 rounded-lg">#{{ $category->sort_order ?? 0 }}</span>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-slate-800/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-white">{{ $category->meals_count }}</p>
                    <p class="text-[10px] text-slate-500">منتج</p>
                </div>
                <div class="bg-slate-800/50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-white">{{ $category->subcategories_count }}</p>
                    <p class="text-[10px] text-slate-500">فئة فرعية</p>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center">
                <svg class="w-7 h-7 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">لا توجد فئات</p>
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($categories->hasPages())
<div class="flex items-center justify-between">
    <p class="text-xs text-slate-400">
        عرض {{ $categories->firstItem() }}–{{ $categories->lastItem() }} من {{ $categories->total() }}
    </p>
    <div class="flex items-center gap-1">
        @if($categories->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">السابق</span>
        @else
        <a href="{{ $categories->previousPageUrl() }}"
           class="px-3 py-1.5 rounded-lg text-xs text-slate-400 bg-slate-800/50 hover:bg-slate-700/50 transition-colors">السابق</a>
        @endif
        @if($categories->hasMorePages())
        <a href="{{ $categories->nextPageUrl() }}"
           class="px-3 py-1.5 rounded-lg text-xs text-white btn-primary">التالي</a>
        @else
        <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">التالي</span>
        @endif
    </div>
</div>
@endif

@endsection
