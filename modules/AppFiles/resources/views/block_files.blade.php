@can("appfiles")
@php
$stats = UploadFile::getFileStorageStats();
@endphp

<form class="d-flex flex-column flex-fill hp-100" action="{{ url_app("files") }}" method="POST">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom position-relative zIndex-5">
        <div class="fs-18 fw-5">{{ __("Media") }}</div>
        <div class="d-flex align-items-center gap-8">

            <div>
                <label for="file-upload">
                    <i class="fa-light fa-arrow-up-from-bracket pointer"></i>
                </label>
                <input id="file-upload" class="d-none form-file-input" name="avatar" type="file" multiple="true" data-call-success="Main.ajaxScroll(true)">
            </div>
            <a class="text-gray-700 text-hover-primary uploadFromURL actionItem" href="{{ url_app("files/upload_from_url") }}" data-id="" data-popup="uploadFileFromURLModal" data-call-success="Main.ajaxScroll(true)">
                <span class="size-16 me-1 text-center"><i class="fa-light fa-link"></i></span>
            </a>

            @if(get_option('file_google_drive_status') && Gate::allows('appfiles.google_drive'))
                <a class="text-gray-700 text-hover-primary" id="google-drive-chooser" href="javascript:void(0);"  data-call-success="Main.ajaxScroll(true)"><i class="fa-brands fa-google-drive"></i></a>
            @endif
            @if(get_option('file_dropbox_status') && Gate::allows('appfiles.dropbox'))
                <a class="text-gray-700 text-hover-primary" id="dropbox-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)"><i class="fa-brands fa-dropbox"></i></a>
            @endif
            @if(get_option('file_onedrive_status') && Gate::allows('appfiles.onedrive'))
                <a class="text-gray-700 text-hover-primary" id="onedrive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)"><i class="fa-regular fa-cloud"></i></a>
            @endif

            @if(Gate::allows('appmediasearch') && !empty(SearchMedia::services()))
            <a class="text-gray-700 text-hover-primary actionItem" href="{{ route("app.search_media.popup_search") }}" data-popup="searchMediaModel"><i class="fa-light fa-magnifying-glass"></i></a>
            @endif
            
            <div class="btn-group position-static d-flex align-items-center gap-8">
                <div class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-grid-2"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ url_app("files/update_folder") }}" data-popup="updateFolderModal">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-folder-plus"></i></span>
                            <span >{{ __('New folder') }}</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ url_app("files/destroy") }}" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                            <span>{{ __('Delete') }}</span>
                        </a>
                    </li>
                </ul>
                <div class="d-block d-sm-block d-md-none ">
                    <div class="btn btn-icon btn-sm btn-light btn-hover-danger b-r-50 a-rotate showCompose">
                        <i class="fa-light fa-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 fs-12 hp-100 ajax-scroll files file-widget position-relative" data-select-multi="1" data-url="{{ url_app("files/mini_list") }}" data-resp=".file-list" data-scroll=".ajax-scroll">
        <div class="row file-list"></div>
    </div>
    <div class="d-flex flex-column-fluid justify-content-between align-items-center mt-auto border-top p-3 text-gray-500 fs-12 position-relative">
        <div class="ajax-scroll-loading d-none position-absolute b-0 l-0 zIndex-200 w-100">
            <div class="app-loading progress-primary-500 mx-auto pl-0 pr-0 w-100"></div>
        </div>

        <div>
            <div class="fw-5">{{ __("Total") }}</div>
            <div class="text-gray-900 fw-6">{{ sprintf("%d ". __("files"), $stats['total_files']) }}</div>
        </div>
        <div class="text-end">
            <div class="fw-5">{{ __("Used") }}</div>
            <div class="text-gray-900 fw-6">{{ $stats['used_friendly'] }}/{{ $stats['max_friendly'] }}</div>
        </div>
    </div>  
</form>
@endcan