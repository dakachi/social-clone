@extends('layouts.app')

@section('content')

<div class="container max-w-800">
	
	<div class="mt-4 mb-4">
        <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
            <div class="my-3 d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                	@if( empty($result) )
                		{{ __('Create') }}
                	@else
                		{{ __('Edit') }}
                	@endif
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Design and manage labels for clear page publishing.') }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-8">
                <a class="btn btn-light btn-sm d-flex align-items-center justify-content-between" href="{{ module_url() }}">
                	<span><i class="fa-light fa-angle-left"></i></span>
                	<span>
                		{{ __('Back') }}
                	</span>
                </a>
            </div>
        </div>
    </div>

    <form class="actionForm" action="{{ module_url("save") }}" data-redirect="{{ module_url() }}">
    	<input class="d-none" name="id_secure" type="text" value="{{ data($result, "id_secure") }}">
		<div class="card mt-5 mb-5">
         	<div class="card-body">
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
	                     	<input placeholder="{{ __('Name') }}" class="form-control" name="name" id="name" type="text" value="{{ data($result, "name") }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
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
         	<div class="card-footer justify-content-end">
          		<button type="submit" class="btn btn-dark">
	                {{ __('Save changes') }}
	            </button>
         	</div>
        </div>

    </form>

</div>

@endsection