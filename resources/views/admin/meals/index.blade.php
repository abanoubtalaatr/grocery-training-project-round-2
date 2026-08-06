@extends('admin.layouts.app')

@section('title', 'إدارة المنتجات')
@section('page-title', 'المنتجات')
@section('page-description', 'إدارة قائمة المنتجات والوجبات')

@section('content')

{{-- Filters --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form method="GET" action="{{ route('admin.meals.index') }}" class="flex flex-wrap gap-3 flex-1">
        <div class="relative flex-1 min-w-[200px]">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث بالاسم أو الماركة..."
                   class="w-full bg-slate-900/60 border border-slate-700/50 text-slate-200 placeholder-slate-500 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-emerald-500/50">
        </div>
        <select name="category" class="bg-slate-900/60 border border-slate-700/50 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500/50 min-w-[160px]" onchange="this.form.submit()">
            <option value="">كل الفئات</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>
        <select name="status" class="bg-slate-900/60 border border-slate-700/50 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500/50 min-w-[140px]" onchange="this.form.submit()">
            <option value="">كل الحالات</option>
            <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>متاح</option>
            <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>غير متاح</option>
            <option value="featured"    {{ request('status') === 'featured'    ? 'selected' : '' }}>مميز</option>
            <option value="out_of_stock"{{ request('status') === 'out_of_stock'? 'selected' : '' }}>نفد المخزون</option>
        </select>
        <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-medium">تصفية</button>
        @if(request()->hasAny(['search', 'category', 'status']))
        <a href="{{ route('admin.meals.index') }}"
           class="px-4 py-2.5 rounded-xl text-slate-400 text-sm border border-slate-700/50 hover:bg-slate-800/50 transition-colors">مسح</a>
        @endif
    </form>
</div>

{{-- Grid/Table Toggle & Count --}}
<div class="rounded-2xl overflow-hidden"
     style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">

    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
        <div>
            <h3 class="text-sm font-bold text-white">قائمة المنتجات</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $meals->total() }} منتج</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="gridViewBtn" onclick="switchView('grid')"
                    class="p-2 rounded-lg text-emerald-400 bg-emerald-400/10 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </button>
            <button id="listViewBtn" onclick="switchView('list')"
                    class="p-2 rounded-lg text-slate-500 hover:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Grid View --}}
    <div id="gridView" class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($meals as $meal)
        <div class="rounded-xl overflow-hidden hover:ring-1 hover:ring-emerald-500/30 transition-all group"
             style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
            {{-- Image --}}
            <div class="relative h-44 bg-slate-800/50 overflow-hidden">
                @if($meal->image)
                <img src="{{ $meal->image_url }}" alt="{{ $meal->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.src='https://via.placeholder.com/300x200/1e293b/475569?text=No+Image'">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                @endif
                {{-- Badges --}}
                <div class="absolute top-2 right-2 flex flex-col gap-1">
                    @if($meal->is_featured)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-amber-950">مميز</span>
                    @endif
                    @if(!$meal->is_available)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/90 text-white">غير متاح</span>
                    @endif
                    @if($meal->stock_quantity <= 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-700 text-slate-300">نفد</span>
                    @endif
                </div>
            </div>
            {{-- Info --}}
            <div class="p-3.5">
                <h4 class="text-sm font-semibold text-white truncate mb-1">{{ $meal->title }}</h4>
                <p class="text-xs text-slate-500 mb-2 truncate">{{ $meal->category?->name ?? '—' }}</p>
                <div class="flex items-center justify-between">
                    <div>
                        @if($meal->discount_price)
                        <span class="text-xs text-slate-500 line-through">${{ number_format($meal->price, 2) }}</span>
                        <span class="text-sm font-bold text-emerald-400 mr-1">${{ number_format($meal->discount_price, 2) }}</span>
                        @else
                        <span class="text-sm font-bold text-white">${{ number_format($meal->price, 2) }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 text-xs text-slate-400">
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        {{ number_format($meal->rating, 1) }}
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500">
                    <span>مخزون: {{ $meal->stock_quantity }}</span>
                    <span>مُباع: {{ $meal->sold_count }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-slate-500">لا توجد منتجات</div>
        @endforelse
    </div>

    {{-- List View (hidden by default) --}}
    <div id="listView" class="hidden overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-right border-b border-slate-800/40">
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">المنتج</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden md:table-cell">الفئة</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">السعر</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden sm:table-cell">المخزون</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">الحالة</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden lg:table-cell">التقييم</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/30">
                @forelse($meals as $meal)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($meal->image)
                            <img src="{{ $meal->image_url }}" alt="{{ $meal->title }}"
                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                                 onerror="this.style.display='none'">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-white">{{ $meal->title }}</p>
                                <p class="text-xs text-slate-500">{{ $meal->brand ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-xs text-slate-400">{{ $meal->category?->name ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4">
                        @if($meal->discount_price)
                        <div>
                            <span class="text-sm font-bold text-emerald-400">${{ number_format($meal->discount_price, 2) }}</span>
                            <span class="text-xs text-slate-500 line-through mr-1">${{ number_format($meal->price, 2) }}</span>
                        </div>
                        @else
                        <span class="text-sm font-bold text-white">${{ number_format($meal->price, 2) }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell">
                        <span class="text-sm {{ $meal->stock_quantity <= 0 ? 'text-red-400' : ($meal->stock_quantity <= 10 ? 'text-amber-400' : 'text-white') }} font-semibold">
                            {{ $meal->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @if($meal->is_available)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-400/15 text-emerald-400">متاح</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-400/15 text-red-400">غير متاح</span>
                        @endif
                        @if($meal->is_featured)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-400/15 text-amber-400 mr-1">مميز</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-xs text-slate-300">{{ number_format($meal->rating, 1) }}</span>
                            <span class="text-[10px] text-slate-500">({{ $meal->rating_count }})</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-16 text-center text-slate-500 text-sm">لا توجد منتجات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($meals->hasPages())
    <div class="px-5 py-4 border-t border-slate-800/40 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            عرض {{ $meals->firstItem() }}–{{ $meals->lastItem() }} من {{ $meals->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($meals->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">السابق</span>
            @else
            <a href="{{ $meals->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-slate-400 bg-slate-800/50 hover:bg-slate-700/50 transition-colors">السابق</a>
            @endif
            @if($meals->hasMorePages())
            <a href="{{ $meals->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-white btn-primary">التالي</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">التالي</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function switchView(type) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn  = document.getElementById('gridViewBtn');
    const listBtn  = document.getElementById('listViewBtn');

    if (type === 'grid') {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridBtn.classList.add('text-emerald-400', 'bg-emerald-400/10');
        gridBtn.classList.remove('text-slate-500');
        listBtn.classList.remove('text-emerald-400', 'bg-emerald-400/10');
        listBtn.classList.add('text-slate-500');
    } else {
        listView.classList.remove('hidden');
        gridView.classList.add('hidden');
        listBtn.classList.add('text-emerald-400', 'bg-emerald-400/10');
        listBtn.classList.remove('text-slate-500');
        gridBtn.classList.remove('text-emerald-400', 'bg-emerald-400/10');
        gridBtn.classList.add('text-slate-500');
    }
}
</script>
@endpush
