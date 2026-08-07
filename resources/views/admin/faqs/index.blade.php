@extends('layouts.admin')

@section('title', 'FAQs')
@section('breadcrumb', 'FAQs')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">FAQ Management</h1>
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add New FAQ
    </a>
</div>

<!-- Filters -->
<div class="table-container" style="padding: 20px; margin-bottom: 25px;">
    <form action="{{ route('admin.faqs.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
            <label for="search" class="form-label">Search FAQs</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Question or answer...">
        </div>
        <div class="form-group" style="width: 180px; margin-bottom: 0;">
            <label for="category" class="form-label">Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="width: 150px; margin-bottom: 0;">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Deleted</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            @if(request()->anyFilled(['search', 'category', 'status']))
                <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- FAQs Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                    <tr @if($faq->trashed()) style="opacity: 0.6;" @endif>
                        <td style="font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                        <td style="max-width: 400px;">
                            <span style="font-weight: 600; color: var(--text-primary);">{{ Str::limit($faq->question, 80) }}</span>
                            @if($faq->trashed())
                                <span class="badge badge-danger" style="font-size: 0.65rem; margin-left: 5px;">Deleted</span>
                            @endif
                        </td>
                        <td>
                            @if($faq->category)
                                <span class="badge badge-secondary">{{ $faq->category }}</span>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-weight: 600;">{{ $faq->order }}</td>
                        <td>
                            @if($faq->trashed())
                                <span class="badge badge-danger">Deleted</span>
                            @elseif($faq->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                @if(!$faq->trashed())
                                    <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" onsubmit="return confirm('Delete this FAQ?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.faqs.restore', $faq->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i> Restore</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fa-solid fa-circle-question"></i>
                            <p>No FAQs found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($faqs->hasPages())
        <div style="padding: 15px 20px; border-top: 1px solid var(--border-color);">{{ $faqs->links() }}</div>
    @endif
</div>
@endsection
