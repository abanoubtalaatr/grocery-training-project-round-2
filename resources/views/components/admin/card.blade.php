@props(['class' => '', 'title' => null, 'bodyClass' => ''])
<div class="card border-0 shadow-sm {{ $class ?? '' }}">
    @if(isset($title))
        <div class="card-header bg-white border-bottom-1">
            <h5 class="card-title mb-0">{{ $title }}</h5>
        </div>
    @endif
    <div class="card-body {{ $bodyClass ?? '' }}">
        {{ $slot }}
    </div>
</div>
