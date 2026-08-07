@props(['id' => 'confirmModal', 'title' => '', 'message' => '', 'action' => '#', 'method' => null, 'type' => 'danger', 'icon' => 'trash', 'buttonText' => 'Confirm'])
<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom-1 bg-light">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">{{ $message }}</p>
            </div>
            <div class="modal-footer border-top-1 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>
                <form method="POST" action="{{ $action }}" style="display: inline;">
                    @csrf
                    @if(isset($method))
                        @method($method)
                    @endif
                    <button type="submit" class="btn btn-{{ $type ?? 'danger' }}">
                        <i class="bi bi-{{ $icon ?? 'trash' }}"></i> {{ $buttonText ?? 'Confirm' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
