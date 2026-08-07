@extends('layouts.admin')

@section('title', 'Smart Lists')
@section('breadcrumb', 'Smart Lists')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Smart Lists</h1>
</div>

<!-- Search -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.smart-lists.index') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label for="search" class="form-label">Search Lists</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name...">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
    </form>
</div>

<!-- Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>User</th>
                    <th>Items Count</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($smartLists as $list)
                    <tr>
                        <td style="font-weight: 600;">{{ $list->name }}</td>
                        <td>{{ $list->user->name ?? 'Guest' }}</td>
                        <td><span class="badge badge-info">{{ $list->items_count }} items</span></td>
                        <td>{{ $list->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-list-check"></i>
                            <p>No Smart Lists found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($smartLists->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $smartLists->links() }}
        </div>
    @endif
</div>
@endsection
