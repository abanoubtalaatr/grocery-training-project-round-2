@props(['title' => '', 'subtitle' => null, 'action' => null])
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h1 class="mb-1">{{ $title }}</h1>
        @if(isset($subtitle))
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
