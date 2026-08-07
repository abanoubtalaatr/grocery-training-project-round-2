@extends('layouts.admin')

@section('title', 'User Profile')
@section('breadcrumb', 'Users / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">{{ $user->name }}</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 25px;">
    <!-- Profile Card -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="table-container" style="padding: 25px; text-align: center;">
            <img src="{{ $user->profile_image_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower($user->email)).'?d=mp&s=120' }}"
                 alt="{{ $user->name }}"
                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary); margin-bottom: 15px;">
            <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 5px;">{{ $user->name }}</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">{{ $user->email }}</p>
            <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                @if($user->is_admin)
                    <span class="badge badge-primary"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                @else
                    <span class="badge badge-secondary"><i class="fa-solid fa-user"></i> Customer</span>
                @endif
                @if($user->is_active ?? true)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; text-transform: uppercase; color: var(--text-muted);">Stats</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Total Orders:</span>
                    <span style="font-weight: 700;">{{ $user->orders_count }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Favorites:</span>
                    <span style="font-weight: 700;">{{ $user->favorites_count }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Addresses:</span>
                    <span style="font-weight: 700;">{{ $user->addresses->count() }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Joined:</span>
                    <span style="font-weight: 600; font-size: 0.85rem;">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($user->id !== auth()->id())
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 15px; text-transform: uppercase; color: var(--text-muted);">Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST" onsubmit="return confirm('Toggle admin role?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn {{ $user->is_admin ? 'btn-warning' : 'btn-primary' }}" style="width: 100%;">
                        <i class="fa-solid {{ $user->is_admin ? 'fa-user-minus' : 'fa-user-shield' }}"></i>
                        {{ $user->is_admin ? 'Remove Admin Role' : 'Make Admin' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn {{ ($user->is_active ?? true) ? 'btn-danger' : 'btn-success' }}" style="width: 100%;">
                        <i class="fa-solid {{ ($user->is_active ?? true) ? 'fa-ban' : 'fa-check' }}"></i>
                        {{ ($user->is_active ?? true) ? 'Deactivate Account' : 'Activate Account' }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Contact Info -->
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Contact Information</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach([
                    ['Email', $user->email, 'fa-envelope'],
                    ['Phone', $user->phone ?? '—', 'fa-phone'],
                    ['Gender', ucfirst($user->gender ?? '—'), 'fa-venus-mars'],
                    ['Date of Birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : '—', 'fa-calendar'],
                ] as [$label, $value, $icon])
                <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 10px; border-bottom: 1px dashed var(--border-color);">
                    <i class="fa-solid {{ $icon }}" style="width: 20px; color: var(--color-primary);"></i>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">{{ $label }}</div>
                        <div style="font-weight: 600; color: var(--text-primary);">{{ $value }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="table-container">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary);">Recent Orders</h3>
                @if($user->orders_count > 0)
                    <a href="{{ route('admin.orders.index') }}?search={{ $user->name }}" class="btn btn-secondary btn-sm">View All</a>
                @endif
            </div>
            @if($user->orders->count() > 0)
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->orders as $order)
                        <tr>
                            <td style="font-weight: 700; color: var(--color-primary);">{{ $order->order_number }}</td>
                            <td>
                                @php $sc = ['placed'=>'badge-warning','processing'=>'badge-info','delivered'=>'badge-success','cancelled'=>'badge-danger']; @endphp
                                <span class="badge {{ $sc[$order->status] ?? 'badge-secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td style="font-weight: 700;">${{ number_format($order->total, 2) }}</td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $order->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="empty-state" style="padding: 30px;">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p>No orders yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
