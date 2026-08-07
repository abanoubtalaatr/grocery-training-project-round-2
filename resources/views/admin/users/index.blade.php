@extends('components.admin.layout')

@section('title', 'Users')
@section('breadcrumb_title', 'Users')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <x-admin.page-header title="Users Management" subtitle="Manage customer accounts and permissions.">
    </x-admin.page-header>

    {{-- Filters --}}
    <x-admin.filter-form action="{{ route('admin.users.index') }}" resetUrl="{{ route('admin.users.index') }}">
        <x-admin.filter-input name="search" label="Search" width="4" lgWidth="4" 
            placeholder="Search username, email, phone, name..." />
        
        <x-admin.filter-input name="status" label="Status" type="select" width="2" lgWidth="2">
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="email_verified" label="Email" type="select" width="2" lgWidth="2">
            <option value="1" {{ request('email_verified') == '1' ? 'selected' : '' }}>Verified</option>
            <option value="0" {{ request('email_verified') == '0' ? 'selected' : '' }}>Unverified</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="sort_by" label="Sort By" type="select" width="2" lgWidth="2">
            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Join Date</option>
            <option value="username" {{ request('sort_by') == 'username' ? 'selected' : '' }}>Username</option>
            <option value="email" {{ request('sort_by') == 'email' ? 'selected' : '' }}>Email</option>
            <option value="loyalty_points" {{ request('sort_by') == 'loyalty_points' ? 'selected' : '' }}>Loyalty Points</option>
        </x-admin.filter-input>
        
        <x-admin.filter-input name="sort_order" label="Order" type="select" width="1" lgWidth="1">
            <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Desc</option>
            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Asc</option>
        </x-admin.filter-input>
    </x-admin.filter-form>

    {{-- Table --}}
    @if($users->count() > 0)
        <x-admin.table :pagination="$users">
            <thead class="table-light">
                <tr>
                    <th scope="col">Username</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-end">Loyalty Points</th>
                    <th scope="col">Joined</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->username }}</strong>
                        </td>
                        <td>
                            {{ $user->full_name ?? '—' }}
                        </td>
                        <td>
                            <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                            @if(!$user->email_verified)
                                <br><x-admin.badge variant="warning">Unverified</x-admin.badge>
                            @endif
                        </td>
                        <td>
                            @if($user->phone)
                                <a href="tel:{{ $user->phone }}" class="text-decoration-none">{{ $user->phone }}</a>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                                <x-admin.badge variant="success">Active</x-admin.badge>
                            @else
                                <x-admin.badge variant="secondary">Inactive</x-admin.badge>
                            @endif
                            @if($user->is_admin)
                                <x-admin.badge variant="dark">Admin</x-admin.badge>
                            @endif
                        </td>
                        <td class="text-end">
                            <x-admin.badge variant="light">{{ $user->loyalty_points }}</x-admin.badge>
                        </td>
                        <td>
                            <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                        </td>
                        <td class="text-center">
                            <x-admin.actions 
                                id="{{ $user->id }}"
                                show="{{ route('admin.users.show', $user) }}"
                                edit="{{ route('admin.users.edit', $user) }}"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>
    @else
        <x-admin.card>
            <x-admin.empty-state 
                title="No users found"
                description="There are currently no users in the system."
                icon="bi-people"
            />
        </x-admin.card>
    @endif
</div>
@endsection

