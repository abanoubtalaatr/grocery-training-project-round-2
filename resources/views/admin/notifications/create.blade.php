@extends('layouts.admin')

@section('title', 'Send Notification')
@section('breadcrumb', 'Notifications / Send')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Send Broadcast Notification</h1>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <form action="{{ route('admin.notifications.store') }}" method="POST" id="notif-form">
        @csrf

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="title" class="form-label">Notification Title <span style="color: var(--color-danger)">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Special Offer – 20% off today!">
                @error('title') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full">
                <label for="body" class="form-label">Message Body <span style="color: var(--color-danger)">*</span></label>
                <textarea name="body" id="body" class="form-control" rows="5" required placeholder="Full notification message...">{{ old('body') }}</textarea>
                @error('body') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Notification Type</label>
                <input type="text" name="type" id="type" class="form-control" value="{{ old('type', 'admin_broadcast') }}" placeholder="e.g. promotion, announcement">
                @error('type') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="target" class="form-label">Target Audience <span style="color: var(--color-danger)">*</span></label>
                <select name="target" id="target" class="form-control" onchange="toggleUserSelect(this.value)">
                    <option value="all" {{ old('target') === 'all' ? 'selected' : '' }}>All Customers (Broadcast)</option>
                    <option value="specific" {{ old('target') === 'specific' ? 'selected' : '' }}>Specific Users</option>
                </select>
                @error('target') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full" id="user-select-container" style="{{ old('target') === 'specific' ? '' : 'display: none;' }}">
                <label for="user_ids" class="form-label">Select Users <span style="color: var(--color-danger)">*</span></label>
                <select name="user_ids[]" id="user_ids" class="form-control" multiple style="height: 200px;">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ in_array($user->id, (array) old('user_ids', [])) ? 'selected' : '' }}>
                            {{ $user->name }} — {{ $user->email }}
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple users.</small>
                @error('user_ids') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Send this notification?')">
                <i class="fa-solid fa-paper-plane"></i> Send Notification
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function toggleUserSelect(value) {
    const container = document.getElementById('user-select-container');
    container.style.display = value === 'specific' ? 'flex' : 'none';
}
</script>
@endsection
