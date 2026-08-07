@extends('components.admin.layout')

@section('title', $user->username)
@section('breadcrumb_title', $user->username)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">{{ $user->full_name ?? $user->username }}</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @include('admin.partials.flash-messages')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Personal Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Personal Information</h5>
                    <dl class="row">
                        <dt class="col-sm-3">Username:</dt>
                        <dd class="col-sm-9"><strong>{{ $user->username }}</strong></dd>

                        <dt class="col-sm-3">Full Name:</dt>
                        <dd class="col-sm-9">{{ $user->full_name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Email:</dt>
                        <dd class="col-sm-9">
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            @if($user->email_verified)
                                <span class="badge bg-success ms-2">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark ms-2">Unverified</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Phone:</dt>
                        <dd class="col-sm-9">
                            {{ $user->phone ?? 'N/A' }}
                            @if($user->phone_verified)
                                <span class="badge bg-success ms-2">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark ms-2">Unverified</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Gender:</dt>
                        <dd class="col-sm-9">{{ $user->gender ? ucfirst($user->gender) : 'Not specified' }}</dd>

                        <dt class="col-sm-3">Birthday:</dt>
                        <dd class="col-sm-9">{{ $user->birthday ? $user->birthday->format('F d, Y') : 'Not specified' }}</dd>

                        <dt class="col-sm-3">Country:</dt>
                        <dd class="col-sm-9">{{ $user->country_code ?? 'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Account Status</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div>
                                @if($user->is_active)
                                    <span class="badge bg-success p-2">Active</span>
                                    <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-2" onclick="return confirm('Deactivate this user?');">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary p-2">Inactive</span>
                                    <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success ms-2">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admin Status</label>
                            <div>
                                @if($user->is_admin)
                                    <span class="badge bg-danger p-2">Admin</span>
                                    <form method="POST" action="{{ route('admin.users.toggleAdmin', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning ms-2" onclick="return confirm('Remove admin privileges?');">
                                            Demote
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary p-2">User</span>
                                    <form method="POST" action="{{ route('admin.users.toggleAdmin', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-2" onclick="return confirm('Promote to admin?');">
                                            Promote
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="mb-2">Verification Status</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Email Verified:</small>
                                <br>
                                @if($user->email_verified)
                                    <span class="badge bg-success">Yes</span>
                                    <small class="text-muted">{{ $user->email_verified_at?->format('F d, Y H:i A') }}</small>
                                @else
                                    <span class="badge bg-warning text-dark">No</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Phone Verified:</small>
                                <br>
                                @if($user->phone_verified)
                                    <span class="badge bg-success">Yes</span>
                                    <small class="text-muted">{{ $user->phone_verified_at?->format('F d, Y H:i A') }}</small>
                                @else
                                    <span class="badge bg-warning text-dark">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rewards Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Rewards & Credits</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small">Loyalty Points</p>
                            <h3 class="text-success">{{ $user->loyalty_points }}</h3>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small">Store Credits</p>
                            <h3 class="text-primary">${{ number_format($user->store_credits, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Statistics</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small">Total Orders</p>
                            <h3>{{ $orderCount }}</h3>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small">Total Spent</p>
                            <h3 class="text-success">${{ number_format($totalSpent, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Info</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-6">Role:</dt>
                        <dd class="col-sm-6">
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </dd>

                        <dt class="col-sm-6">Email:</dt>
                        <dd class="col-sm-6">
                            @if($user->email_verified)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Activity Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Activity</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Joined:</dt>
                        <dd class="col-sm-6">
                            <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                        </dd>

                        <dt class="col-sm-6">Last Update:</dt>
                        <dd class="col-sm-6">
                            <small class="text-muted">{{ $user->updated_at->format('M d, Y') }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit User
                        </a>
                        @if($user->is_active)
                            <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Deactivate this user?');">
                                    <i class="bi bi-lock"></i> Deactivate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                    <i class="bi bi-unlock"></i> Activate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
