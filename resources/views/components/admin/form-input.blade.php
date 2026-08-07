@props(['name' => 'input', 'label' => '', 'required' => false, 'type' => 'text', 'rows' => 3, 'placeholder' => null, 'disabled' => false, 'value' => null, 'help' => null, 'step' => null, 'min' => null, 'max' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if(isset($required) && $required)<span class="text-danger">*</span>@endif
    </label>
    
    @if($type === 'textarea')
        <textarea 
            id="{{ $name }}" 
            name="{{ $name }}"
            class="form-control @error($name) is-invalid @enderror"
            rows="{{ $rows ?? 3 }}"
            @if(isset($required) && $required) required @endif
            @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if(isset($disabled) && $disabled) disabled @endif
        >{{ old($name, $value ?? '') }}</textarea>
    @elseif($type === 'select')
        <select 
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-select @error($name) is-invalid @enderror"
            @if(isset($required) && $required) required @endif
            @if(isset($disabled) && $disabled) disabled @endif
        >
            @if(isset($placeholder))
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
    @elseif($type === 'checkbox')
        <div class="form-check">
            <input 
                type="checkbox" 
                id="{{ $name }}"
                name="{{ $name }}"
                value="1"
                class="form-check-input @error($name) is-invalid @enderror"
                @if(old($name, $value) == 1) checked @endif
                @if(isset($disabled) && $disabled) disabled @endif
            />
            <label class="form-check-label" for="{{ $name }}">
                {{ $label }}
            </label>
        </div>
    @else
        <input 
            type="{{ $type ?? 'text' }}" 
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-control @error($name) is-invalid @enderror"
            value="{{ old($name, $value ?? '') }}"
            @if(isset($required) && $required) required @endif
            @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if(isset($disabled) && $disabled) disabled @endif
            @if(isset($step)) step="{{ $step }}" @endif
            @if(isset($min)) min="{{ $min }}" @endif
            @if(isset($max)) max="{{ $max }}" @endif
        />
    @endif
    
    @error($name)
        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
    @enderror
    
    @if(isset($help))
        <small class="form-text text-muted">{{ $help }}</small>
    @endif
</div>
