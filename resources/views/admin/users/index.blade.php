@extends('layouts.admin')

@section('title', 'Users Management')
@section('breadcrumb', 'Users')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Users Management</h1>
    <span style="color: var(--text-muted); font-weight: 500;">Total: {{ $users->total() }} users</span>
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.users.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search Users</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email or phone...">
        </div>
        <div class="form-group" style="width: 160px; margin-bottom: 0;">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-control">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customers</option>
            </select>
        </div>
        <div class="form-group" style="width: 160px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="display: flex; align-items: center; gap: 12px;">
                            <img src="{{ $user->profile_image_url ?? 'https://www.gravatar.com/avatar/'.md5(strtolower($user->email)).'?d=mp&s=44' }}"
                                 alt="{{ $user->name }}"
                                 style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                            <div>
                                <span style="font-weight: 700; color: var(--text-primary);">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                    <span class="badge badge-info" style="font-size: 0.65rem; margin-left: 5px;">You</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $user->email }}</div>
                            @if($user->phone)
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $user->phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge badge-primary"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                            @else
                                <span class="badge badge-secondary"><i class="fa-solid fa-user"></i> Customer</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active ?? true)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: var(--text-primary);">{{ $user->orders_count }}</td>
                        <td style="font-weight: 700; color: var(--color-success);">${{ number_format($user->orders_sum_total ?? 0, 2) }}</td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary btn-sm" title="View Profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Toggle admin role for {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-warning' : 'btn-primary' }}" title="{{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}">
                                            <i class="fa-solid {{ $user->is_admin ? 'fa-user-minus' : 'fa-user-shield' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ ($user->is_active ?? true) ? 'btn-danger' : 'btn-success' }}" title="{{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}">
                                            <i class="fa-solid {{ ($user->is_active ?? true) ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-users"></i>
                            <p>No users found matching your criteria.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
