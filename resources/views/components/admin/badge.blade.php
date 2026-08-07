@props(['variant' => 'secondary', 'icon' => null])
<span class="badge bg-{{ $variant ?? 'secondary' }} rounded-pill">
    @if(isset($icon))
        <i class="bi bi-{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</span>
