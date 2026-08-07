@extends('layouts.admin')

@section('title', 'Favorites')
@section('breadcrumb', 'Favorites')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">User Favorites</h1>
</div>

<!-- Search -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.favorites.index') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label for="search" class="form-label">Search Favorites</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="User name or product title...">
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
                    <th>User</th>
                    <th>Product / Meal</th>
                    <th>Favorited At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($favorites as $favorite)
                    <tr>
                        <td style="font-weight: 600;">{{ $favorite->user->name ?? 'Guest' }}</td>
                        <td>{{ $favorite->meal->title ?? 'Deleted Meal' }}</td>
                        <td>{{ $favorite->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-state">
                            <i class="fa-solid fa-heart"></i>
                            <p>No Favorites found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($favorites->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">
            {{ $favorites->links() }}
        </div>
    @endif
</div>
@endsection
