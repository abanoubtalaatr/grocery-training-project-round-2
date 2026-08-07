@php($pageTitle = 'Overview')

<x-admin.layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Overview</h1>
            <p class="text-muted mb-0">A clean snapshot of your store health and recent activity.</p>
        </div>
        <a href="{{ route('admin.logout') }}" class="btn btn-outline-dark btn-sm rounded-pill" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right me-1"></i> Sign out
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    @php($s = $data['stats'])

    <div class="row g-4 mb-4">
        @foreach([
            ['label' => 'Users', 'value' => number_format($s['total_users']), 'meta' => number_format($s['active_users']).' active', 'icon' => 'bi-people'],
            ['label' => 'Orders', 'value' => number_format($s['total_orders']), 'meta' => number_format($s['pending_orders']).' pending', 'icon' => 'bi-bag'],
            ['label' => 'Revenue', 'value' => '$'.number_format($s['total_revenue'], 2), 'meta' => '', 'icon' => 'bi-cash'],
            ['label' => 'Meals', 'value' => number_format($s['total_meals']), 'meta' => number_format($s['available_meals']).' available', 'icon' => 'bi-cup-straw'],
            ['label' => 'Categories', 'value' => number_format($s['total_categories'] ?? ($s['total_categories'] ?? 0)), 'meta' => '', 'icon' => 'bi-tags'],
            ['label' => 'Reviews', 'value' => number_format($s['total_reviews'] ?? 0), 'meta' => number_format($s['pending_reviews']).' pending', 'icon' => 'bi-star'],
            ['label' => 'Messages', 'value' => number_format($s['total_messages'] ?? 0), 'meta' => number_format($s['new_messages']).' new', 'icon' => 'bi-chat-left-text'],
        ] as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                            <h4 class="mb-1">{{ $card['value'] }}</h4>
                            <div class="small text-muted">{{ $card['meta'] }}</div>
                        </div>
                        <div class="rounded-3 p-2 bg-dark text-white">
                            <i class="bi {{ $card['icon'] }} fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h2 class="h6 mb-3">Orders & Revenue (Last 12 months)</h2>
                <canvas id="ordersChart" height="120"></canvas>
                <hr class="my-4">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                <h3 class="h6 mb-2">Top Meals</h3>
                <div class="d-flex flex-column gap-3">
                    @foreach($data['top_meals'] as $meal)
                        <div class="d-flex justify-content-between align-items-start border rounded-3 p-3">
                            <div>
                                <div class="fw-semibold">{{ $meal['title'] }}</div>
                                <div class="small text-muted">{{ $meal['category'] }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">{{ $meal['sold_count'] }}</div>
                                <div class="small text-muted">sold</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h3 class="h6 mb-2">Latest Users</h3>
                <div class="list-group list-group-flush">
                    @foreach($data['latest_users'] as $u)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                            <div>
                                <div class="fw-semibold">{{ $u['username'] }}</div>
                                <div class="small text-muted">{{ $u['email'] }}</div>
                            </div>
                            <div class="small text-muted">{{ $u['created_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h3 class="h6 mb-3">Recent Orders</h3>
                <div class="list-group">
                    @foreach($data['recent_orders'] as $order)
                        <div class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $order['order_number'] }}</div>
                                <div class="small text-muted">{{ $order['customer'] }} • {{ $order['items_count'] }} items</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">${{ number_format($order['total'], 2) }}</div>
                                <div class="small text-muted">{{ $order['created_at'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h3 class="h6 mb-3">Recent Reviews</h3>
                <div class="list-group">
                    @foreach($data['recent_reviews'] as $r)
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="fw-semibold">{{ $r['meal'] }}</div>
                            <div class="small text-muted">By {{ $r['user'] }} • {{ $r['created_at'] }}</div>
                            <div class="mt-1 small text-muted">{{ $r['comment'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h3 class="h6 mb-3">Recent Messages</h3>
                <div class="list-group">
                    @foreach($data['recent_messages'] as $m)
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="fw-semibold">{{ $m['subject'] }}</div>
                            <div class="small text-muted">{{ $m['name'] }} • {{ $m['email'] }}</div>
                            <div class="mt-1 small text-muted">{{ $m['created_at'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels = @json($data['monthly']['labels']);
            const ordersData = @json($data['monthly']['orders']);
            const revenueData = @json($data['monthly']['revenue']);

            // Orders chart
            const ordersCtx = document.getElementById('ordersChart');
            if (ordersCtx) {
                new Chart(ordersCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Orders',
                            data: ordersData,
                            borderColor: '#000',
                            backgroundColor: 'rgba(0,0,0,0.05)',
                            tension: 0.25,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // Revenue chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            backgroundColor: '#0d6efd',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        })();
    </script>
</x-admin.layout>
