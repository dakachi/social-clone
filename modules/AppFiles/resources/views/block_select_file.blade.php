@php
    $id_custom = str_replace("[", "_", $id);
    $id_custom = str_replace("]", "", $id_custom);
@endphp

@if(isset($name))
<label for="name" class="form-label">{{ __($name) }}
    @if(isset($required) && $required)
    (<span class="text-danger">*</span>)
    @endif
</label>
@endif
<div class="input-group mb-3">
    <input class="form-control" id="{{ $id_custom }}" name="{{ $id }}" placeholder="{{ __("Select file") }}" type="text" value="{{ $value ?? '' }}">
    <a class="btn btn-input actionItem pointer" href="{{ route("app.files.popup_files") }}" data-id="{{ $id_custom }}" data-filter="{{ serialize([ "type" => "image", "multi" => isset($multi)?$multi:false ]) }}" data-popup="filesModal">
        <i class="fa-light fa-folder-open"></i> {{ __("Files") }}
    </a>
</div>
