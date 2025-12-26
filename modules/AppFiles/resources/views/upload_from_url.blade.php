<div class="modal fade" id="uploadFileFromURLModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered ">
		<form class="modal-content actionForm" action="{{ module_url("save_file") }}" data-call-success="Main.closeModal('uploadFileFromURLModal'); Main.ajaxScroll(true);">
     		<input class="form-control d-none" name="folder_id" id="folder_id" type="text" value="{{ $folder_id }}">
			<div class="modal-header">
				<h1 class="modal-title fs-16">
					<i class="fa-light fa-link"></i> {{ __("Upload file from URL") }}
				</h1>

				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
	                  	<div class="mb-0">
	                  		<label for="file_url" class="form-label">{{ __('File URL') }}</label>
                     		<input placeholder="{{ __('Enter file url') }}" class="form-control" name="file_url" id="file_url" type="text">
	                  	</div>
 					</div>

 				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="submit" class="btn btn-dark">{{ __('Upload') }}</button>
			</div>
		</form>
	</div>
</div>