<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Grocery Shop') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;850&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <!-- Custom Page CSS -->
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-store" style="font-size: 1.5rem; color: #3b82f6;"></i>
                <h2>Grocery Shop</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.subcategories.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-folder-tree"></i>
                        <span>Subcategories</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.meals.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.meals.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-bowl-food"></i>
                        <span>Meals / Products</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-users"></i>
                        <span>Users Management</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.smart-lists.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.smart-lists.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Smart Lists</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.favorites.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.favorites.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-heart"></i>
                        <span>Favorites</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.faqs.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-circle-question"></i>
                        <span>FAQs</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.support.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-headset"></i>
                        <span>Support Requests</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.notifications.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-bell"></i>
                        <span>Broadcasting Alerts</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <a href="{{ route('admin.analytics') }}" class="sidebar-link">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.monitoring') ? 'active' : '' }}">
                    <a href="{{ route('admin.monitoring') }}" class="sidebar-link">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>System Risks</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="sidebar-link">
                        <i class="fa-solid fa-sliders"></i>
                        <span>General Settings</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <form action="{{ route('admin.logout') }}" method="POST" onsubmit="return confirm('Are you sure you want to log out?');">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; display: flex; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="main-wrapper">
            <!-- Top Navbar -->
            <header class="top-header">
                <div class="header-search">
                    <button type="button" class="menu-toggle" id="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <span style="font-weight: 500; color: var(--text-muted);">Admin Panel &bull; @yield('breadcrumb')</span>
                </div>
                <div class="header-user">
                    <div class="user-profile">
                        <img src="{{ auth()->user()->profile_image_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?d=mp' }}" alt="Avatar" class="user-avatar">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Body -->
            <main class="content-body">
                <!-- Session Alerts -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <span><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <span><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</span>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                <!-- Dynamic Content Injection -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Javascript UI Toggles -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('admin-sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');
            if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
