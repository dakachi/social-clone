@php
$file_id = request("file_id") ?? "";
$file_id_slug = request("file_id") ? "-". request("file_id") : "";
@endphp

@if($folder)
<input type="radio" class="d-none form-check-input ajax-scroll-filter{{$file_id_slug}}" name="folder_id" value="{{ $folder->id_secure }}" checked>
<nav aria-label="breadcrumb" class="mb-0">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
        <label class="fs-12 text-primary fw-5 text-hover-primary pointer" id="breadcrumb_folder_0">
            {{ __("Root Folder") }}
            <input class="d-none form-check-input ajax-scroll-filter{{$file_id_slug}}" type="radio" name="folder_id" value="0" id="breadcrumb_folder_0">
        </label>
    </li>
    @foreach ($parent_folders as $parent)
        <li class="breadcrumb-item">
            <label class="fs-12 text-primary fw-5 text-hover-primary-900 pointer" id="breadcrumb_folder_{{ $parent->id_secure }}">
                {{ $parent->name }}
                <input class="d-none form-check-input ajax-scroll-filter{{$file_id_slug}}" type="radio" name="folder_id" value="{{ $parent->id_secure }}" id="breadcrumb_folder_{{ $parent->id_secure }}">
            </label>
        </li>
    @endforeach
    <li class="breadcrumb-item fs-12 text-gray-400 active" aria-current="page">{{ $folder->name }}</li>
  </ol>
</nav>
@endif

@if($folders)
    @foreach($folders as $value)
    <div class="col-4 col-md-3 px-2">
        <div class="ratio ratio-1x1 mb-3">
            <label class="d-flex flex-column flex-fill w-100 bg-light border b-r-10 w-100 pointer mb-3" for="folder_{{ $value->id_secure }}">
                <input class="d-none form-check-input ajax-scroll-filter{{$file_id_slug}}" type="radio" name="folder_id" value="{{ $value->id_secure }}" id="folder_{{ $value->id_secure }}">
                <div class="d-flex flex-fill align-items-center justify-content-center p-2 bg-warning-100">
                    <div class="fs-38 text-warning">
                        <i class="fa-light fa-folder-open"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-2 border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate">{{ $value->name }}</div>
                        <div class="fs-8 d-flex align-items-center gap-8 text-gray-600 lh-sm text-truncate">
                            <span>{{ sprintf("%d Files", $value->file_count) }} </span>
                            <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                            <span>{{ sprintf("%d Folder", $value->folder_count) }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-120 min-w-120">
                                <li>
                                    <a class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 actionItem" href="{{ module_url("update_folder") }}" data-popup="updateFolderModal" data-call-success="Main.ajaxScroll(true, {{ $file_id }})"
                                        <span class="text-truncate-1">{{ __('Edit Image') }}</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item px-2 p-t-4 p-b-4 rounded d-flex gap-8 fw-5 fs-13 actionItem" href="{{ url_admin("files/destroy") }}" data-id="" data-call-success="Main.ajaxScroll(true, {{ $file_id }})">
                                        <span class="text-truncate-1">{{ __('Delete') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </label>
        </div>
        
    </div>
    @endforeach
@endif

@if($files)
    @foreach($files as $key => $value)

    @php
    $detectType = Media::detectFileIcon($value->detect);
    @endphp
    
    <div class="col-4 col-md-3 px-2">
        <div class="file-item w-100 ratio ratio-1x1 min-h-80 mb-3 border b-r-6" data-id="file_{{ $value->id_secure }}" data-name="medias">
            <label class="d-flex flex-column flex-fill" for="{{ $value->id_secure }}">
                <div class="position-absolute r-6 t-6 zIndex-1">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" name="id[]" type="checkbox" value="{{ Media::url($value->file)  }}" id="file_{{ $value->id_secure }}">
                    </div>
                </div>

                <div class="d-flex flex-fill align-items-center justify-content-center overflow-y-auto bg-cover position-relative btl-r-6 btr-r-6 file-item-media text-{{ $detectType['color'] }} bg-{{ $detectType['color'] }}-100" style="background-image: url( {{ Media::url($value->file)  }} );">
                    @if($value->detect != "image")
                    <div class="fs-30">
                        <i class="{{ $detectType['icon'] }}"></i>
                    </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto p-1 gap-8 position-relative zIndex-2 file-info border-top">
                    <div class="text-truncate">
                        <div class="fs-9 text-gray-800 fw-5 lh-sm text-truncate">{{ $value->name }}</div>
                        <div class="fs-8 text-gray-600 lh-sm text-truncate">{{ Number::fileSize($value->size); }}</div>
                    </div>

                    <div>
                        <div class="btn-group position-static">
                            <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-12" data-bs-toggle="dropdown" aria-expanded="true">
                                <i class="fa-light fa-grid-2"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 wp-100 max-w-150 min-w-150 text-truncate-1">
                                @can('appfiles.image_editor')
                                <li>
                                    <button type="button" class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-12 text-truncate-1 editImage" data-file="{{ Media::url($value->file) }}" data-file-id="{{ $file_id }}" data-id="{{ $value->id_secure }}">
                                        <span class="size-16 text-center"><i class="fa-light fa-edit"></i></span>
                                        <span class="fw-5 text-truncate-1">{{ __("Edit Image") }}</span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @endcan
                                <li>
                                    <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-12 text-truncate-1 actionItem" href="{{ module_url("destroy") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true, {{ $file_id }})">
                                        <span class="size-16 text-center"><i class="fa-light fa-trash-can"></i></span>
                                        <span class="fw-5 text-truncate-1">{{ __("Delete") }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>
    @endforeach
@else
    <div class="empty mt-100 mb-100"></div>
@endif