<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        @if(isset($breadcrumbs))
            @foreach($breadcrumbs as $item)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                    @if($loop->last)
                        {{ $item['label'] }}
                    @else
                        <a href="{{ $item['url'] }}" class="text-decoration-none">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        @else
            <li class="breadcrumb-item active">Dashboard</li>
        @endif
    </ol>
</nav>
