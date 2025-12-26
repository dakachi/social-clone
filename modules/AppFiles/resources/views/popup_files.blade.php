@php
	$file_id = time();
@endphp

<div class="modal fade" id="filesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<form class="modal-content actionForm" action="{{ module_url("files") }}" data-call-success="Main.closeModal('filesModal'); Main.ajaxScroll(true, {{ $file_id }});" data-id="{{ $id }}" data-type="{{ ($filter && isset($filter['type']))?$filter['type']:"all" }}" data-multi="{{ ($filter && isset($filter['multi']))?$filter['multi']:false }}">
			<div class="modal-header justify-content-between align-items-center p-3 border-bottom position-relative zIndex-3">
		        <div class="fs-20">{{ __("Media") }}</div>
		        <div class="d-flex align-items-center gap-8">
		            <div>
		                <label for="file-upload">
		                    <i class="fa-light fa-arrow-up-from-bracket pointer"></i>
		                </label>
		                <input id="file-upload" class="d-none form-file-input" name="avatar" type="file" multiple="true" data-call-success="Main.ajaxScroll(true, {{ $file_id }})">
		            </div>
		            <a class="text-gray-700 text-hover-primary uploadFromURL actionItem" href="{{ url_app("files/upload_from_url") }}" data-id="" data-popup="uploadFileFromURLModal" data-call-success="Main.ajaxScroll(true, {{ $file_id }})">
		                <span class="size-16 me-1 text-center"><i class="fa-light fa-link"></i></span>
		            </a>

		            @if(get_option('file_google_drive_status') && Gate::allows('appfiles.google_drive'))
		                <a class="text-gray-700 text-hover-primary" id="google-drive-chooser" href="javascript:void(0);"  data-call-success="Main.ajaxScroll(true, {{ $file_id }})"><i class="fa-brands fa-google-drive"></i></a>
		            @endif
		            @if(get_option('file_dropbox_status') && Gate::allows('appfiles.dropbox'))
		                <a class="text-gray-700 text-hover-primary" id="dropbox-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true, {{ $file_id }})"><i class="fa-brands fa-dropbox"></i></a>
		            @endif
		            @if(get_option('file_onedrive_status') && Gate::allows('appfiles.onedrive'))
		                <a class="text-gray-700 text-hover-primary" id="onedrive-chooser" href="javascript:void(0);" data-call-success="Main.ajaxScroll(true, {{ $file_id }})"><i class="fa-regular fa-cloud"></i></a>
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
		                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ url_app("files/destroy") }}" data-call-success="Main.ajaxScroll(true, {{ $file_id }})">
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
		            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		        </div>
			</div>
			<div class="modal-body p-0">

			    <div class="d-flex flex-column flex-column-fluid overflow-y-auto p-3 fs-12 ajax-scroll-{{ $file_id }} files file-widget position-relative min-h-400 max-h-400" data-select-multi="1" data-url="{{ route("app.files.popup_list", ["file_id" => $file_id]) }}" data-resp=".file-list-{{$file_id}}" data-scroll=".ajax-scroll-{{ $file_id }}">
			        <div class="row file-list-{{$file_id}}"></div>
			    </div>

			    <div class="ajax-scroll-loading d-none position-absolute b-0 l-0 zIndex-200 w-100">
		            <div class="app-loading progress-primary-500 mx-auto pl-0 pr-0 w-100"></div>
		        </div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="button" class="btn btn-dark btnAddFiles">{{ __('Add Files') }}</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	Main.ajaxScroll(true, {{ $file_id }});
	Main.ajaxScrollActions({{ $file_id }});
	Files.Actions();
</script>
