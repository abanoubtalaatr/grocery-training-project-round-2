@props(['type' => 'button', 'variant' => 'primary', 'size' => '', 'class' => '', 'disabled' => false, 'modal' => null, 'icon' => null])
<button type="{{ $type ?? 'button' }}" 
        class="btn btn-{{ $variant ?? 'primary' }} {{ $size ?? '' }} {{ $class ?? '' }}"
        @if(isset($disabled) && $disabled) disabled @endif
        @if(isset($modal)) data-bs-toggle="modal" data-bs-target="#{{ $modal }}" @endif>
    @if(isset($icon))
        <i class="bi bi-{{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>
