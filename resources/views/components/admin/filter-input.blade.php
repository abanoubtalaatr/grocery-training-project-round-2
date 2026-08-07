@props(['width' => 2, 'lgWidth' => null, 'type' => 'text', 'name' => 'filter', 'label' => 'Filter', 'placeholder' => null])
<div class="col-md-{{ $width ?? 2 }} col-lg-{{ $lgWidth ?? $width ?? 2 }}">
    @if($type === 'select')
        <select name="{{ $name }}" class="form-select form-select-sm" aria-label="Filter by {{ $label }}">
            <option value="">{{ $placeholder ?? 'All ' . $label }}</option>
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type ?? 'text' }}" 
               name="{{ $name }}" 
               class="form-control form-control-sm" 
               placeholder="{{ $placeholder }}"
               value="{{ request($name) }}"
               aria-label="Filter by {{ $label }}"
        />
    @endif
</div>
