@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="d-flex justify-content-center my-3">
        <ul class="pagination" style="gap: 4px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="background: var(--color-primary); color: #fff;">&laquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="background: var(--color-primary); color: #fff;">&laquo;</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="background: var(--border-color); color: var(--text-muted);">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link" style="background: var(--color-primary); color: #fff;">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}" style="background: var(--border-color); color: var(--text-primary);">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" style="background: var(--color-primary); color: #fff;">&raquo;</a></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="background: var(--color-primary); color: #fff;">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
