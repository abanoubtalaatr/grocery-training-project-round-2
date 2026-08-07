@extends('layouts.admin')

@section('title', 'Support Message')
@section('breadcrumb', 'Support / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">Message from {{ $support->name }}</h1>
    <a href="{{ route('admin.support.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
    <!-- Message Content -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="table-container" style="padding: 25px;">
            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary);">{{ $support->subject }}</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">
                    Received {{ $support->created_at->format('M d, Y \a\t H:i') }}
                </p>
            </div>
            <div style="line-height: 1.8; color: var(--text-secondary); white-space: pre-wrap;">{{ $support->message }}</div>
        </div>

        <!-- Admin Notes & Update Status -->
        <div class="table-container" style="padding: 25px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Admin Response / Notes</h3>
            <form action="{{ route('admin.support.update-status', $support->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="admin_notes" class="form-label">Internal Notes</label>
                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="5" placeholder="Add internal notes or copy of reply...">{{ old('admin_notes', $support->admin_notes) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Update Status</label>
                    <select name="status" id="status" class="form-control" style="margin-bottom: 12px;">
                        @foreach(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'spam' => 'Spam'] as $val => $label)
                            <option value="{{ $val }}" {{ $support->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Notes & Status
                </button>
            </form>
        </div>
    </div>

    <!-- Sender Info -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: var(--text-primary);">Sender Details</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach([
                    ['Name', $support->name, 'fa-user'],
                    ['Email', $support->email, 'fa-envelope'],
                    ['Phone', $support->phone ?? '—', 'fa-phone'],
                    ['IP Address', $support->ip_address ?? '—', 'fa-globe'],
                ] as [$label, $value, $icon])
                <div style="display: flex; align-items: flex-start; gap: 10px; padding-bottom: 10px; border-bottom: 1px dashed var(--border-color);">
                    <i class="fa-solid {{ $icon }}" style="color: var(--color-primary); margin-top: 2px; width: 16px;"></i>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">{{ $label }}</div>
                        <div style="font-weight: 600; word-break: break-all;">{{ $value }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="table-container" style="padding: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">Current Status</h3>
            @php $sc = ['new'=>'badge-primary','read'=>'badge-secondary','replied'=>'badge-success','spam'=>'badge-danger']; @endphp
            <span class="badge {{ $sc[$support->status] ?? 'badge-secondary' }}" style="font-size: 0.9rem; padding: 8px 16px;">{{ ucfirst($support->status) }}</span>
        </div>

        <div class="table-container" style="padding: 20px;">
            <form action="{{ route('admin.support.destroy', $support->id) }}" method="POST" onsubmit="return confirm('Permanently delete this message?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="width: 100%;">
                    <i class="fa-solid fa-trash-can"></i> Delete Message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
