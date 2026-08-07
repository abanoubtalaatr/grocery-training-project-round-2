<aside 
    x-show="sidebarOpen"
    x-transition
    class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col"
>
    <!-- Logo -->
    <div class="p-6 border-b border-gray-700">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                <i class="fas fa-shield-haltered text-white"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white">Admin Panel</h1>
                <p class="text-xs text-gray-400">Management System</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-home w-5"></i>
            <span class="ml-3">Dashboard</span>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-users w-5"></i>
            <span class="ml-3">Users</span>
        </a>

        <!-- Orders -->
        <a href="{{ route('admin.orders.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.orders.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-shopping-cart w-5"></i>
            <span class="ml-3">Orders</span>
        </a>

        <!-- Products -->
        <a href="{{ route('admin.products.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.products.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-box w-5"></i>
            <span class="ml-3">Products</span>
        </a>

        <!-- Categories -->
        <a href="{{ route('admin.categories.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-tags w-5"></i>
            <span class="ml-3">Categories</span>
        </a>

        <!-- Subcategories -->
        <a href="{{ route('admin.subcategories.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.subcategories.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-tag w-5"></i>
            <span class="ml-3">Subcategories</span>
        </a>

        <!-- Offers -->
        <a href="{{ route('admin.offers.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.offers.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-percent w-5"></i>
            <span class="ml-3">Offers</span>
        </a>

        <!-- Reviews -->
        <a href="{{ route('admin.reviews.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.reviews.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-star w-5"></i>
            <span class="ml-3">Reviews</span>
        </a>

        <!-- Contacts -->
        <a href="{{ route('admin.contacts.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.contacts.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-envelope w-5"></i>
            <span class="ml-3">Messages</span>
        </a>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" 
           class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700 text-white' : '' }}">
            <i class="fas fa-cog w-5"></i>
            <span class="ml-3">Settings</span>
        </a>
    </nav>

    <!-- User Info -->
    <div class="p-4 border-t border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center">
                <i class="fas fa-user text-gray-300"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
        </form>
    </div>
</aside>
