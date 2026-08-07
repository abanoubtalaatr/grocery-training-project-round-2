@extends('layouts.admin')

@section('title', 'General Settings')
@section('breadcrumb', 'Settings')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">General System Settings</h1>
</div>

<div class="form-card">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="site_name" class="form-label">Store / App Name</label>
                <input type="text" name="site_name" id="site_name" class="form-control" value="{{ old('site_name', $settings->site_name ?? config('app.name')) }}">
                @error('site_name') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                <input type="text" name="currency_symbol" id="currency_symbol" class="form-control" value="{{ old('currency_symbol', $settings->currency_symbol ?? '$') }}">
                @error('currency_symbol') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="support_email" class="form-label">Support Email</label>
                <input type="email" name="support_email" id="support_email" class="form-control" value="{{ old('support_email', $settings->support_email ?? '') }}">
                @error('support_email') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="support_phone" class="form-label">Support Phone</label>
                <input type="text" name="support_phone" id="support_phone" class="form-control" value="{{ old('support_phone', $settings->support_phone ?? '') }}">
                @error('support_phone') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="shipping_fee" class="form-label">Default Shipping Fee ($)</label>
                <input type="number" name="shipping_fee" id="shipping_fee" class="form-control" value="{{ old('shipping_fee', $settings->shipping_fee ?? 0) }}" step="0.01" min="0">
                @error('shipping_fee') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                <input type="number" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', $settings->tax_rate ?? 0) }}" step="0.01" min="0" max="100">
                @error('tax_rate') <span class="form-feedback-invalid">{{ $message }}</span> @enderror
            </div>

            <div class="form-group form-group-full" style="flex-direction: row; align-items: center; gap: 8px; margin-top: 10px;">
                <input type="checkbox" name="store_is_open" id="store_is_open" style="width: auto;" value="1" {{ old('store_is_open', $settings->store_is_open ?? true) ? 'checked' : '' }}>
                <label for="store_is_open" class="form-label" style="margin-bottom: 0; font-weight: 700; cursor: pointer;">Store Open for Orders</label>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>
@endsection
