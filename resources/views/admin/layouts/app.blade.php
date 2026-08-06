<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — Grocery Admin</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50:  '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(-8px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        * { font-family: 'Cairo', 'Inter', sans-serif; }
        :root {
            --sidebar-width: 260px;
        }
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-dark {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .gradient-text {
            background: linear-gradient(135deg, #10b981, #06b6d4, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-item {
            transition: all 0.2s ease;
            position: relative;
        }
        .nav-item::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: #10b981;
            border-radius: 2px 0 0 2px;
            transition: height 0.2s ease;
        }
        .nav-item.active::before,
        .nav-item:hover::before {
            height: 60%;
        }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.1));
        }
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.4); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.7); }
        .shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        table tbody tr {
            transition: background 0.15s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #059669, #0891b2);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #047857, #0e7490);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 h-full antialiased">

<div class="flex h-screen overflow-hidden">

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar"
           class="sidebar-transition fixed inset-y-0 right-0 z-50 w-64 flex flex-col"
           style="background: linear-gradient(180deg, #0f172a 0%, #0a0f1e 100%); border-left: 1px solid rgba(16,185,129,0.15);">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800/60">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg, #059669, #0891b2);">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-white">Grocery Admin</h1>
                <p class="text-xs text-slate-400">لوحة التحكم</p>
            </div>
            <button id="sidebarClose"
                    class="mr-auto text-slate-500 hover:text-slate-300 transition-colors lg:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Admin Info -->
        <div class="px-4 py-3 mx-3 mt-3 rounded-xl glass">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                     style="background: linear-gradient(135deg, #059669, #0891b2);">
                    {{ mb_substr(auth()->user()?->username ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()?->username ?? 'Admin' }}</p>
                    <p class="text-xs text-emerald-400">مدير النظام</p>
                </div>
                <span class="w-2 h-2 rounded-full bg-emerald-400 badge-pulse flex-shrink-0"></span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mb-2">القائمة الرئيسية</p>

            @php
                $currentRoute = request()->route()->getName() ?? '';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>لوحة التحكم</span>
            </a>

            <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mt-4 mb-2">المبيعات</p>

            <a href="{{ route('admin.orders.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.orders') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>الطلبات</span>
                @php $pendingCount = \App\Models\Order::where('status', 'placed')->count(); @endphp
                @if($pendingCount > 0)
                <span class="mr-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                      style="background: rgba(239,68,68,0.2); color: #f87171;">{{ $pendingCount }}</span>
                @endif
            </a>

            <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mt-4 mb-2">المنتجات</p>

            <a href="{{ route('admin.meals.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.meals') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>المنتجات</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.categories') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>الفئات</span>
            </a>

            <a href="{{ route('admin.reviews.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.reviews') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span>التقييمات</span>
            </a>

            <p class="px-3 text-[10px] uppercase tracking-widest text-slate-500 font-semibold mt-4 mb-2">المستخدمون</p>

            <a href="{{ route('admin.users.index') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm {{ str_starts_with($currentRoute, 'admin.users') ? 'active text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>المستخدمون</span>
            </a>
        </nav>

        <!-- Bottom: Filament Link + Logout -->
        <div class="px-3 pb-4 space-y-1 border-t border-slate-800/60 pt-3">
            <a href="/admin" target="_blank"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>Filament Panel</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-400 hover:text-red-400 hover:bg-red-400/10 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden backdrop-blur-sm"></div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex flex-col flex-1 overflow-hidden lg:mr-64">

        <!-- ===== HEADER ===== -->
        <header class="flex items-center justify-between px-6 py-4 border-b border-slate-800/60 flex-shrink-0"
                style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px);">

            <div class="flex items-center gap-4">
                <!-- Mobile menu toggle -->
                <button id="menuToggle"
                        class="lg:hidden text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title & Breadcrumb -->
                <div>
                    <h2 class="text-lg font-bold text-white">@yield('page-title', 'لوحة التحكم')</h2>
                    <p class="text-xs text-slate-400 hidden sm:block">@yield('page-description', 'نظرة عامة على أداء المتجر')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Date -->
                <div class="hidden md:flex items-center gap-2 text-xs text-slate-400 bg-slate-800/50 px-3 py-1.5 rounded-lg">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ now()->format('d M Y') }}
                </div>

                <!-- Notifications Bell -->
                <button class="relative p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @php $pendingBell = \App\Models\Order::where('status', 'placed')->count(); @endphp
                    @if($pendingBell > 0)
                    <span class="absolute top-1 right-1 w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center text-white"
                          style="background: #ef4444;">{{ $pendingBell }}</span>
                    @endif
                </button>
            </div>
        </header>

        <!-- ===== PAGE CONTENT ===== -->
        <main class="flex-1 overflow-y-auto p-6">
            <div class="animate-fade-in">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="px-6 py-3 border-t border-slate-800/40 flex items-center justify-between text-xs text-slate-500">
            <span>© {{ date('Y') }} Grocery Admin Dashboard</span>
            <span class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 badge-pulse"></span>
                النظام يعمل بشكل طبيعي
            </span>
        </footer>
    </div>
</div>

<script>
    // Mobile Sidebar Toggle
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
    }
    function closeSidebar() {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('hidden');
    }

    menuToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Init mobile state
    if (window.innerWidth < 1024) {
        sidebar.classList.add('translate-x-full');
    }
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('translate-x-full');
            overlay.classList.add('hidden');
        } else {
            sidebar.classList.add('translate-x-full');
        }
    });
</script>

@stack('scripts')
</body>
</html>
