@props(['action' => '', 'resetUrl' => ''])
<x-admin.card title="Filters" class="mb-4">
    <form method="GET" action="{{ $action }}" class="row g-3">
        {{ $slot }}
        
        <div class="col-12">
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="{{ $resetUrl }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
        </div>
    </form>
</x-admin.card>
