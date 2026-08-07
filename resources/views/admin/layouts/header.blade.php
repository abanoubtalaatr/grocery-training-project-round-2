<header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
    
    <!-- Toggle Sidebar -->
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
        <i class="fas fa-bars text-xl"></i>
    </button>

    <!-- Search -->
    <div class="flex-1 max-w-lg mx-4">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
            <input 
                type="text" 
                placeholder="Search..."
                class="w-full pl-10 pr-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
            >
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <button class="relative text-gray-400 hover:text-white">
            <i class="fas fa-bell text-xl"></i>
            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-xs flex items-center justify-center">3</span>
        </button>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" class="text-gray-400 hover:text-white">
            <i class="fas fa-cog text-xl"></i>
        </a>
    </div>
</header>
