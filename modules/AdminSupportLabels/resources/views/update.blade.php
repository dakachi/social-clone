<div class="modal fade" id="SupportLabelsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('SupportLabelsModal'); Main.ajaxScroll(true);">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Create Labels") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label class="form-label">{{ __('Status') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1" {{ data($result, "status", "radio", 1, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_1">
				                    	{{ __('Enable') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" {{ data($result, "status", "radio", 0, 1) }}>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Disable') }}
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input placeholder="{{ __('Enter Label Name') }}" class="form-control" name="name" id="name" type="text" value="{{ data($result, "name") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Icon') }}</label>
		                  	<div class="text-gray-600 fs-12 mb-2">{{ __("You can find icon at here: ") }}<a href="https://fontawesome.com/v5/search" target="_blank">https://fontawesome.com/v5/search</a></div>
	                     	<input placeholder="{{ __('Enter Font Awesome Icon') }}" class="form-control" name="icon" id="icon" type="text" value="{{ data($result, "icon") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-0">
		                  	<label class="form-label">{{ __('Highlight Color') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column color-type">
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="primary" id="color_primary" {{ data($result, "color", "radio", "primary", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_primary">
				                    	<div class="size-40 b-r-6 border bg-primary-100 border-2 border-primary activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="success" id="color_success" {{ data($result, "color", "radio", "success", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_success">
				                    	<div class="size-40 b-r-6 border bg-success-100 activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="danger" id="color_danger" {{ data($result, "color", "radio", "danger", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_danger">
				                    	<div class="size-40 b-r-6 border bg-danger-100 activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="warning" id="color_warning" {{ data($result, "color", "radio", "warning", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_warning">
				                    	<div class="size-40 b-r-6 border bg-warning-100 activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="info" id="color_info" {{ data($result, "color", "radio", "info", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_info">
				                    	<div class="size-40 b-r-6 border bg-info-100 activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				                <div class="form-check ps-0">
				                  	<input class="form-check-input d-none" type="radio" name="color" value="dark" id="color_dark" {{ data($result, "color", "radio", "dark", "primary") }}>
				                  	<label class="form-check-label mt-1 ps-0" for="color_dark">
				                    	<div class="size-40 b-r-6 border bg-dark-100 activeItem" data-parent=".color-type" data-add="border-2 border-primary" for="color_primary"></div>
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
				<button type="submit" class="btn btn-dark">{{ __('Save changes') }}</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	Main.activeItem();
</script>
