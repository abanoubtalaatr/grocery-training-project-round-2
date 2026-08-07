@extends('layouts.admin')

@section('title', 'Broadcasting Alerts')
@section('breadcrumb', 'Notifications')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Broadcasting Alerts</h1>
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-bell"></i> Send Notification
    </a>
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.notifications.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title...">
        </div>
        <div class="form-group" style="width: 180px; margin-bottom: 0;">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-control">
                <option value="">All Types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'type']))
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Notifications Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Recipient</th>
                    <th>Read</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr @if(!$notification->is_read) style="font-weight: 600;" @endif>
                        <td>
                            <span style="color: var(--text-primary);">{{ $notification->title }}</span>
                            @if($notification->body)
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0; font-weight: 400;">{{ Str::limit($notification->body, 60) }}</p>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info" style="font-size: 0.75rem;">{{ ucfirst(str_replace('_', ' ', $notification->type ?? 'general')) }}</span>
                        </td>
                        <td>
                            @if($notification->user)
                                <span style="font-size: 0.85rem;">{{ $notification->user->name }}</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($notification->is_read)
                                <span class="badge badge-success">Read</span>
                            @else
                                <span class="badge badge-warning">Unread</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $notification->created_at->format('M d, Y H:i') }}</td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Delete this notification?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fa-solid fa-bell-slash"></i>
                            <p>No notifications found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
