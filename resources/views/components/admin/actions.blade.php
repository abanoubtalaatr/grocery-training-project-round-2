@props(['show' => null, 'edit' => null, 'delete' => null, 'id' => 0, 'custom' => null])
<div class="d-flex gap-2 flex-wrap">
    @if(isset($show))
        <a href="{{ $show }}" class="btn btn-sm btn-outline-info" title="View details">
            <i class="bi bi-eye"></i> View
        </a>
    @endif
    
    @if(isset($edit))
        <a href="{{ $edit }}" class="btn btn-sm btn-outline-warning" title="Edit item">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endif
    
    @if(isset($delete))
        <button type="button" class="btn btn-sm btn-outline-danger" 
                data-bs-toggle="modal" 
                data-bs-target="#deleteModal{{ $id }}"
                title="Delete item">
            <i class="bi bi-trash"></i> Delete
        </button>
    @endif
    
    @if(isset($custom))
        {{ $custom }}
    @endif
</div>

@if(isset($delete))
    <x-admin.confirm-modal 
        id="deleteModal{{ $id }}"
        title="Delete Confirmation"
        message="Are you sure you want to delete this item? This action cannot be undone."
        action="{{ $delete }}"
        method="DELETE"
        type="danger"
        icon="trash"
        buttonText="Delete"
    />
@endif
