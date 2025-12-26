@if($current_page == 1)
    @if($folder)
    <input type="radio" class="d-none form-check-input ajax-scroll-filter" name="folder_id" value="{{ $folder->id_secure }}" checked>
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <label class="fs-14 text-primary fw-5 text-hover-primary pointer" id="breadcrumb_folder_0">
                {{ __("Root Folder") }}
                <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="0" id="breadcrumb_folder_0">
            </label>
        </li>
        @foreach ($parent_folders as $parent)
            <li class="breadcrumb-item">
                <label class="fs-14 text-primary fw-5 text-hover-primary-900 pointer" id="breadcrumb_folder_{{ $parent->id_secure }}">
                    {{ $parent->name }}
                    <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="{{ $parent->id_secure }}" id="breadcrumb_folder_{{ $parent->id_secure }}">
                </label>
            </li>
        @endforeach
        <li class="breadcrumb-item fs-14 text-gray-400 active" aria-current="page">{{ $folder->name }}</li>
      </ol>
    </nav>
    @endif

    <div class="col-md-12 {{ !$folder?"mt-5":""; }}">
        @if($folders->count() > 0)
        <div class="d-flex pb-3">
            <div class="fs-18 text-gray-900 fw-5">{{ __("Folder") }}</div>  
        </div>
        @endif

        @if($folders)
        <div class="row">
            @foreach($folders as $value)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <label class="position-relative bg-light border b-r-10 p-4 w-100 pointer" for="folder_{{ $value->id_secure }}">
                    <div class="position-absolute r-10 t-10">
                        <input class="d-none form-check-input ajax-scroll-filter" type="radio" name="folder_id" value="{{ $value->id_secure }}" id="folder_{{ $value->id_secure }}">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fs-28 text-warning">
                            <i class="fa-light fa-folder-open"></i>
                        </div>
                        <div class="position-relative">
                            <div class="dropdown dropdown-hover">
                                <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade">
                                    <i class="fa-light fa-grid-2"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end right-0 p-2 border-1 border-gray-300 max-w-80 w-100">
                                    <li>
                                        <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="{{ module_url("update_folder") }}" data-id="{{ $value->id_secure }}" data-popup="updateFolderModal">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                            <span class="fw-5">{{ __("Edit") }}</span>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="{{ module_url("destroy") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true)">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                            <span class="fw-5">{{ __("Delete") }}</span>
                                        </a>
                                    </li>
                                </ul>       
                            </div>
                        </div>
                    </div>
                    <div class="fw-5 fs-14 text-gray-800 mb-1 text-truncate">
                        {{ $value->name }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between fw-5 text-gray-400">
                        <div class="fs-12 d-flex align-items-center gap-8">
                            <span>{{ sprintf("%d Files", $value->file_count) }} </span>
                            <span class="d-inline-block size-4 b-r-50 bg-gray-400"></span> 
                            <span>{{ sprintf("%d Folder", $value->folder_count) }}</span>
                        </div>
                        <div class="fs-11">{{ Number::fileSize($value->total_size); }}</div>
                    </div>
                </label>
            </div>
            @endforeach
        </div>
        @endif

        @if($folders->count() > 0 && $files->count() > 0)
        <div class="d-flex pb-3 pt-3">
            <div class="fs-18 text-gray-900 fw-5">{{ __("Files") }}</div>   
        </div>
        @endif

    </div>

    @if($folders->count() == 0 && $files->count() == 0)
        <div class="empty"></div>
    @endif

@endif

@if($files)
    @foreach($files as $key => $value)

    @php
    $detectType = Media::detectFileIcon($value->detect);
    @endphp

    <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-4">
        <div class="bg-light border b-r-10 position-relative">
            <label 
                class="w-100 h-150 border-bottom btl-r-10 btr-r-10 d-flex align-items-center justify-content-center 
                       text-{{ $detectType['color'] }} bg-{{ $detectType['color'] }}-100 position-relative overflow-hidden" 
                for="file_{{ $value->id_secure }}"
                @if($value->detect == "image")
                    style="background-image: url('{{ Media::url($value->file) }}'); background-size: cover; background-position: center;"
                @endif
            >
                <!-- Checkbox -->
                <div class="position-absolute r-10 t-10 z-10">
                    <input class="form-check-input checkbox-item" type="checkbox" 
                           name="id[]" value="{{ $value->id_secure }}" 
                           id="file_{{ $value->id_secure }}">
                </div>

                @if($value->detect == "video")
                    <video class="position-absolute top-0 start-0 w-100 h-150 object-cover" muted loop>
                        <source src="{{ Media::url($value->file) }}" type="video/mp4">
                        {{ __('Your browser does not support the video tag.') }}
                    </video>
                @elseif($value->detect != "image")
                    <div class="fs-40">
                        <i class="{{ $detectType['icon'] }}"></i>
                    </div>
                @endif
            </label>
            <div class="px-2 py-1 d-flex align-items-center justify-content-between gap-8">
                <div class="text-truncate">
                    <div class="text-gray-800 text-truncate fw-5 fs-12">{{ $value->name }}</div>
                    <div class="text-gray-400 text-truncate fs-11">{{ Number::fileSize($value->size) }}</div>
                </div>
                <div class="position-relative">
                    <div class="btn-group">
                        <a class="dropdown-toggle dropdown-arrow-hide text-gray-900" data-bs-toggle="dropdown" data-bs-animation="fade">
                            <i class="fa-light fa-grid-2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end right-0 p-2 border-1 border-gray-300">
                            @can('appfiles.image_editor')
                                @if($value->detect == "image")
                                <li>
                                    <button type="button" class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 editImage" data-file="{{ Media::url($value->file) }}" data-id="{{ $value->id_secure }}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-edit"></i></span>
                                        <span class="fw-5">{{ __("Edit Image") }}</span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @endif
                            @endcan
                            <li>
                                <a class="dropdown-item py-2 px-2 rounded d-flex gap-8 fs-14 actionItem" href="{{ module_url("destroy") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can"></i></span>
                                    <span class="fw-5">{{ __("Delete") }}</span>
                                </a>
                            </li>
                        </ul>       
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif




