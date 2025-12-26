<div class="form-file">
    @if( isset($large) && $large)
    <label class="w-100 mb-1 form-img ratio ratio-1x1" for="{{ $id ?? '' }}">
        <div></div>                                            
    </label>
    @endif
    @if( isset($name) )
    <label for="{{ $id ?? '' }}" class="btn btn-light w-100">
        {{ $name }}
    </label>
    @endif
    <input class="d-none form-file-input-edit" type="text" value="{{ $value ?? '' }}" />
    <input id="{{ $id ?? '' }}" class="d-none form-file-input" name="{{ $id ?? '' }}" type="file" accept="image/*" />
</div>
