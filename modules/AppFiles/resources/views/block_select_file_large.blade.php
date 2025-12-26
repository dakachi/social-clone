@php
    $id_custom = str_replace("[", "_", $id);
    $id_custom = str_replace("]", "", $id_custom);

    if(!isset($ratio))
        $ratio = "1x1";
@endphp

@if(isset($name))
<label for="name" class="form-label">{{ __($name) }}
    @if(isset($required) && $required)
    (<span class="text-danger">*</span>)
    @endif
</label>
@endif
<div class="p-3 border-1 b-r-6">
    <div class="form-file">
        <a class="w-100 mb-1 form-img ratio ratio-{{ $ratio }} d-block actionItem bg-cover {{ $id_custom }}" style="{{ $value!=""?"background: url( ". Media::url($value) ." )":"" }}" href="{{ route("app.files.popup_files") }}" data-id="{{ $id_custom }}" data-filter="{{ serialize([ "type" => "image", "multi" => false ]) }}" data-popup="filesModal">
        </a>
        <input class="form-control d-none" id="{{ $id_custom }}" name="{{ $id }}" placeholder="{{ __("Select file") }}" type="text" value="{{  Media::url($value)  }}">
        <a class="btn btn-input actionItem pointer w-100" href="{{ route("app.files.popup_files") }}" data-id="{{ $id_custom }}" data-filter="{{ serialize([ "type" => "image", "multi" => false ]) }}" data-popup="filesModal">
            <i class="fa-light fa-folder-open"></i> {{ __("Files") }}
        </a>
    </div>
</div>
