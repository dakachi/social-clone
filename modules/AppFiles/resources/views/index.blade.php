@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@section('sub_header')
    <x-sub-header 
        title="{{ __('Files') }}" 
        description="{{ __('Effortlessly organize, manage, and access your files') }}" 
        :count="$total"
    >
        <div class="d-flex">
            <div class="form-control form-control-sm">
                <span class="btn btn-icon">
                    <i class="fa-light fa-magnifying-glass"></i>
                </span>
                <input class="ajax-scroll-filter" name="keyword" placeholder="{{ __('Search') }}" type="text">
                <button class="btn btn-icon">
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input checkbox-all" id="select_all" type="checkbox">
                    </div>
                </button>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-filter"></i> {{ __("Filters") }}
                </button>
                <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
                    <div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                        <span><i class="fa-light fa-filter"></i></span>
                        <span>{{ __("Filters") }}</span>
                    </div>
                    <div class="p-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __("File Type") }}</label>
                            <select class="form-select ajax-scroll-filter" name="file_type">
                                <option value="-1">{{ __("All") }}</option>
                                <option value="image">{{ __("Image") }}</option>
                                <option value="video">{{ __("Video") }}</option>
                                <option value="doc">{{ __("Document") }}</option>
                                <option value="pdf">{{ __("PDF") }}</option>
                                <option value="csv">{{ __("CSV") }}</option>
                                <option value="other">{{ __("Other") }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-grid-2"></i> {{ __("Actions") }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-210" data-popper-placement="bottom-end">
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 text-truncate uploadFromURL actionItem" href="{{ module_url("upload_from_url") }}" data-id="" data-popup="uploadFileFromURLModal" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-link"></i></span>
                            <span class="text-truncate">{{ __("Upload From URL") }}</span>
                        </a>
                    </li>
                    @if(get_option('file_google_drive_status') && Gate::allows('appfiles.google_drive'))
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14"  id="google-drive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-brands fa-google-drive"></i></span>
                            <span class="text-truncate">{{ __("Google Drive") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(get_option('file_dropbox_status') && Gate::allows('appfiles.dropbox'))
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="dropbox-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-brands fa-dropbox"></i></span>
                            <span class="text-truncate">{{ __("Dropbox") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(get_option('file_onedrive_status') && Gate::allows('appfiles.onedrive'))
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="onedrive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-regular fa-cloud"></i></span>
                            <span class="text-truncate">{{ __("OneDrive") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(get_option('file_addobe_express_status') && Gate::allows('appfiles.image_editor'))
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" id="adobe-express" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-wand-magic"></i></span>
                            <span class="text-truncate">{{ __("Adobe Express") }}</span>
                        </a>
                    </li>
                    @endif

                    @if(Gate::allows('appmediasearch') && !empty(SearchMedia::services()))
                        <li>
                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem"
                               href="{{ route("app.search_media.popup_search") }}"
                               data-popup="searchMediaModel">
                                <span class="size-16 me-1 text-center">
                                    <i class="fa-light fa-magnifying-glass"></i>
                                </span>
                                <span class="text-truncate">{{ __("Search Media Online") }}</span>
                            </a>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                            <span>{{ __("Delete") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="d-flex gap-8">
            <div class="btn-group position-static">
                <button class="btn btn-dark btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <span><i class="fa-light fa-plus"></i></span>
                    <span>{{ __('New') }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300  px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                    <div>
                        <label for="file-upload" class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-up-from-bracket"></i></span> {{ __("Upload file") }}
                        </label>
                        <input id="file-upload" class="d-none form-file-input" name="avatar" type="file" multiple="true" data-call-success="Main.ajaxScroll(true)" />
                    </div>
                    <div>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("update_folder") }}" data-popup="updateFolderModal" data-call-success="Main.ajaxScroll(true)">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-folder-plus"></i></span>
                            <span>{{ __("New folder") }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-sub-header>
@endsection


@section('content')
<form class="justify-content-between" action="{{ module_url() }}" method="POST">
    <div class="container justify-content-between">


        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".file-list" data-scroll="document" data-call-success="Files.loadSuccess(result);">
            <div class="row file-list">

            </div>

            <div class="ajax-scroll-loading d-none position-fixed b-0 l-0 zIndex-200 w-100">
                <div class="app-loading progress-primary-500 mx-auto pl-0 pr-0 w-100"></div>
            </div>
        </div>
    </div>
</form>
@endsection
