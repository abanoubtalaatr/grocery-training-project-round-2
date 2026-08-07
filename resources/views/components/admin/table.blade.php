@props(['pagination' => null])
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            {{ $slot }}
        </table>
    </div>
    
    @if(isset($pagination))
        <div class="card-footer bg-white border-top-1 text-center">
            {{ $pagination->links() }}
        </div>
    @endif
</div>
