@props(['icon' => 'bi-inbox', 'title' => 'No items', 'description' => null, 'action' => null])
<div class="text-center py-5">
    <div class="mb-3">
        <i class="bi {{ $icon ?? 'bi-inbox' }}" style="font-size: 3rem; color: #ccc;"></i>
    </div>
    <h5 class="text-muted mb-2">{{ $title }}</h5>
    @if(isset($description))
        <p class="text-muted mb-3">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
