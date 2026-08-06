@extends('admin.layouts.app')

@section('title', 'إدارة المستخدمين')
@section('page-title', 'المستخدمون')
@section('page-description', 'إدارة حسابات المستخدمين والمشتركين')

@section('content')

{{-- Filter Bar --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form method="GET" action="{{ route('admin.users.index') }}"
          class="flex flex-col sm:flex-row gap-3 flex-1">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="ابحث بالاسم أو البريد..."
                   class="w-full bg-slate-900/60 border border-slate-700/50 text-slate-200 placeholder-slate-500 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-slate-900">
        </div>
        <select name="status"
                class="bg-slate-900/60 border border-slate-700/50 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500/50 min-w-[140px]"
                onchange="this.form.submit()">
            <option value="">كل الحالات</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>نشط</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
            <option value="admin"    {{ request('status') === 'admin'    ? 'selected' : '' }}>مدير</option>
        </select>
        <button type="submit"
                class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            تصفية
        </button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2.5 rounded-xl text-slate-400 text-sm border border-slate-700/50 hover:bg-slate-800/50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            مسح
        </a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="rounded-2xl overflow-hidden"
     style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.06);">

    {{-- Table Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800/60">
        <div>
            <h3 class="text-sm font-bold text-white">قائمة المستخدمين</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $users->total() }} مستخدم</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-right border-b border-slate-800/40">
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">المستخدم</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden md:table-cell">البريد</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden lg:table-cell">الطلبات</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden sm:table-cell">الحالة</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400">النوع</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-400 hidden lg:table-cell">تاريخ التسجيل</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/30">
                @forelse($users as $user)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($user->profile_image || $user->avatar)
                            <img src="{{ $user->profile_image_url ?? $user->avatar }}"
                                 alt="{{ $user->username }}"
                                 class="w-9 h-9 rounded-full object-cover ring-2 ring-slate-700"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <div class="w-9 h-9 rounded-full items-center justify-center text-sm font-bold text-white hidden"
                                 style="background: linear-gradient(135deg, #059669, #0891b2); display:none;">
                                {{ mb_substr($user->username ?? 'U', 0, 1) }}
                            </div>
                            @else
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                                 style="background: linear-gradient(135deg, #059669, #0891b2);">
                                {{ mb_substr($user->username ?? 'U', 0, 1) }}
                            </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-white">{{ $user->username }}</p>
                                <p class="text-xs text-slate-400">{{ $user->full_name ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-sm text-slate-300">{{ $user->email }}</span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-sm font-semibold text-white">{{ $user->orders_count }}</span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell">
                        @if($user->is_active)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-400/15 text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            نشط
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-400/15 text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            غير نشط
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($user->is_admin)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-400/15 text-amber-400">
                            مدير
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-400/10 text-slate-400">
                            مستخدم
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="text-xs text-slate-400">{{ $user->created_at->format('d M Y') }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-slate-400 text-sm">لا يوجد مستخدمون</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-slate-800/40 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            عرض {{ $users->firstItem() }}–{{ $users->lastItem() }} من {{ $users->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($users->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">السابق</span>
            @else
            <a href="{{ $users->previousPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-slate-400 bg-slate-800/50 hover:bg-slate-700/50 transition-colors">السابق</a>
            @endif

            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}"
               class="px-3 py-1.5 rounded-lg text-xs text-white btn-primary">التالي</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-xs text-slate-600 bg-slate-800/30 cursor-not-allowed">التالي</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
