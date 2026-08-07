<aside class="d-none d-lg-flex flex-column justify-content-between border-end bg-white px-4 py-4" style="width: 280px;">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark mb-5">
            <div class="rounded-3 bg-dark text-white p-2">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <div class="fw-semibold">Grocery Admin</div>
                <div class="small text-muted">Control Center</div>
            </div>
        </a>

        <nav class="nav flex-column gap-1">
            <a href="{{ route('admin.dashboard') }}" class="nav-link rounded-3 px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'active bg-dark text-white' : 'text-muted' }}">
                <i class="bi bi-house-door me-2"></i> Overview
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link rounded-3 px-3 py-2 {{ request()->routeIs('admin.users.*') ? 'active bg-dark text-white' : 'text-muted' }}">
                <i class="bi bi-people me-2"></i> Customers
            </a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link rounded-3 px-3 py-2 {{ request()->routeIs('admin.orders.*') ? 'active bg-dark text-white' : 'text-muted' }}">
                <i class="bi bi-basket me-2"></i> Orders
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link rounded-3 px-3 py-2 {{ request()->routeIs('admin.categories.*') ? 'active bg-dark text-white' : 'text-muted' }}">
                <i class="bi bi-tags me-2"></i> Categories
            </a>
            <a href="{{ route('admin.meals.index') }}" class="nav-link rounded-3 px-3 py-2 {{ request()->routeIs('admin.meals.*') ? 'active bg-dark text-white' : 'text-muted' }}">
                <i class="bi bi-cup-straw me-2"></i> Meals
            </a>
            <a href="#" class="nav-link rounded-3 px-3 py-2 text-muted">
                <i class="bi bi-chat-left-text me-2"></i> Messages
            </a>
        </nav>
    </div>

    <div class="text-muted small">
        <div class="border rounded-3 p-3">
            <div class="fw-semibold text-dark">Need a hand?</div>
            <div class="mt-1">This dashboard is intentionally lightweight and focused on the essentials.</div>
        </div>
    </div>
</aside>
