<div class="modal fade" id="groupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<form class="modal-content actionForm" action="{{ module_url("save") }}" data-call-success="Main.closeModal('groupModal'); Main.ajaxScroll(true);">
			<div class="modal-header">
				<h1 class="modal-title fs-16">{{ __("Create group") }}</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

		    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input placeholder="{{ __('Name') }}" class="form-control" name="name" id="name" type="text" value="{{ data($result, "name") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
						<label for="name" class="form-label">{{ __('Select channels') }}</label>
						<div class="mb-3">
							<div class="form-control">
		                     	<i class="fa-light fa-magnifying-glass"></i>
		                     	<input placeholder="{{ __("Search") }}" type="text" class="search-input" value="">
			                </div>
						</div>
 						<div class="mb-4 pf-0 b-r-4">
 							@if($accounts)


 							@php

 								$selected_accounts = [];
 								if($result){
 									$selected_accounts = json_decode( $result->accounts );
 								}

 							@endphp

		                  	<ul class="list-group border overflow-y-scroll max-h-250">
		                  		@foreach($accounts as $value)
		                  			<li class=" search-list">
				                        <div class="list-group-item border-start-0 border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8">
									  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_{{ $value->id_secure }}">
									  			<div class="size-35 min-w-35 size-child">
									  				<img src="{{ Media::url($value->avatar) }}" class="border b-r-6">
									  			</div>
									  			<div class="text-truncate">
									  				<div class="fs-12 lh-sm mb-1 fw-5">{{ $value->name }}</div>
									  				<div class="fs-10 lh-sm text-gray-500">{{ __( ucfirst( $value->social_network." ".$value->category ) ) }}</div>
									  			</div>
									  		</label>
									  		<span>
									  			<input class="form-check-input" type="checkbox" name="accounts[]" value="{{ $value->id_secure }}" id="id_{{ $value->id_secure }}" {{ __( in_array($value->pid, $selected_accounts)?"checked":"" ) }} >
									  		</span>
									  	</div>
		                  			</li>
		                  		@endforeach
							</ul>
							@else
							<div class="empty"></div>
							<div class="d-flex justify-content-center mt-2">
								<a href="{{ url_app("channels") }}" class="btn btn-dark btn-sm">
									<i class="fa-light fa-plus"></i>
									{{ __("Add new channel") }}
								</a>
							</div>
							@endif
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
	Main.Emoji();
	Main.activeItem();
</script>
