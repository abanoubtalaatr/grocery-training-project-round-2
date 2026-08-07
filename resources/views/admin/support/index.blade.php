@extends('layouts.admin')

@section('title', 'Support Requests')
@section('breadcrumb', 'Support')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Support Requests</h1>
    @php $newCount = \App\Models\ContactMessage::where('status', 'new')->count(); @endphp
    @if($newCount > 0)
        <span class="badge badge-danger" style="font-size: 1rem; padding: 8px 16px;">{{ $newCount }} New</span>
    @endif
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.support.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Name, email or subject...">
        </div>
        <div class="form-group" style="width: 160px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Replied</option>
                <option value="spam" {{ request('status') === 'spam' ? 'selected' : '' }}>Spam</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.support.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Messages Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr @if($message->status === 'new') style="background: rgba(59,130,246,0.04); font-weight: 600;" @endif>
                        <td>
                            <div style="font-weight: 600;">{{ $message->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $message->email }}</div>
                            @if($message->phone)
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $message->phone }}</div>
                            @endif
                        </td>
                        <td style="max-width: 300px;">
                            {{ Str::limit($message->subject, 60) }}
                            @if($message->status === 'new')
                                <span class="badge badge-primary" style="font-size: 0.6rem; margin-left: 5px;">NEW</span>
                            @endif
                        </td>
                        <td>
                            @php $sc = ['new'=>'badge-primary','read'=>'badge-secondary','replied'=>'badge-success','spam'=>'badge-danger']; @endphp
                            <span class="badge {{ $sc[$message->status] ?? 'badge-secondary' }}">{{ ucfirst($message->status) }}</span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $message->created_at->format('M d, Y H:i') }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('admin.support.show', $message->id) }}" class="btn btn-secondary btn-sm" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.support.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Delete this message?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fa-solid fa-headset"></i>
                            <p>No support messages found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
