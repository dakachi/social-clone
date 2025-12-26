<div class="file-selected-media" id="medias">
    <div class="items clearfix ui-sortable">

        @if(!empty($files))

            @php 
                $results = DB::table('files')->whereIn("file", $files)->get();
            @endphp

            @foreach($results  as $value)

                @php
                    $detectType = Media::detectFileIcon($value->detect);
                @endphp

                <div class="file-item w-100 ratio ratio-1x1 min-h-80 border b-r-6 rounded selected text-{{ $detectType['color'] }} bg-{{ $detectType['color'] }}-100" data-id="file_{{ $value->id_secure }}" data-file="{{ Media::url($value->file) }}" data-type="{{ $value->detect }}">
                    <label class="d-flex flex-column flex-fill">
                        <div class="position-absolute r-6 t-6 zIndex-1">
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" name="medias[]" type="text" value="{{ $value->file }}" id="file_{{ $value->id_secure }}" style="display: none;">
                            </div>
                        </div>
                        <div class="d-flex flex-fill align-items-center justify-content-center overflow-y-auto bg-cover position-relative btl-r-6 btr-r-6 file-item-media" {!! $value->detect=="image"?'style="background-image: url(\''.Media::url($value->file).'\');"':'' !!}>
                            @if($value->detect != "image")
                            <div class="fs-30">
                                <i class="{{ $detectType['icon'] }}"></i>
                            </div>
                            @endif
                        </div>
                    </label>
                    <button type="button" href="javascript:void(0)" class="remove bg-white border b-r-100 text-danger w-20 h-20 fs-12 position-absolute r-0"><i class="fal fa-times"></i></button>
                </div>

            @endforeach

        @endif
        
    </div>
    <div class="drophere px-3 py-4 text-center text-gray-400 text-uppercase fs-12 bg-gray-100 border-3 border-dashed b-r-6" style="{{ $files?"display: none;":"" }}">
        <span class="has-action d-none">{{ __("Drop here") }}</span>
        <span class="no-action">{{ __("Drag media here to post") }}</span>
    </div>
</div>

<button type="button" class="btn btn-dark btn-lg d-lg-none d-md-none d-sm-block mt-3 w-100 showMedia">{{ __("Select media") }}</button>

