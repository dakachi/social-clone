@if( $captions->Total() > 0 )

	@foreach( $captions as $key => $value )
	<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
	    <label class="card shadow-none" for="caption_{{ $value->id_secure }}">
	    	<div class="card-header px-3 gap-16">
	    		<div class="fs-13 fw-5 text-truncate d-flex align-items-center gap-8">
	    			@if($value->type == 2)
	    			<span class="badge badge-outline badge-xs badge-info">
                     	{{ __("AI") }}
                    </span> 
                    @endif
                    <span class="text-truncate">{{ $value->name }}</span>
	    		</div>
	    		<div class="card-toolbar d-flex gap-16">
	    			<div class="btn-group position-static">
                        <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-14" data-bs-toggle="dropdown" aria-expanded="true">
                            <i class="fa-light fa-grid-2"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-1 w-100 max-w-120 min-w-120">
                            <li>
                                <a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex align-items-center gap-8 fw-5 fs-13 actionItem" href="{{ module_url("update") }}" data-id="{{ $value->id_secure }}" data-popup="captionModal" data-call-success="">
                                    <i class="fa-light fa-pen-to-square"></i> <span >{{ __('Edit') }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item px-2 p-t-2 p-b-2 rounded d-flex align-items-center gap-8 fw-5 fs-13 actionItem" href="{{ module_url("destroy") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true);">
                                    <i class="fa-light fa-trash-can"></i> <span>{{ __('Delete') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="{{ $value->id_secure }}" id="caption_{{ $value->id_secure }}">
	    		</div>
	    	</div>
	        <div class="card-body px-3">
	            <div class="d-flex flex-grow-1 align-items-top gap-8">
	                <div class="flex-grow-1 fs-13 text-truncate-5 min-h-100">
	                    <div class="text-truncate-5 text-gray-700">
	                    	<i class="fa-light fa-quote-left text-gray-900"></i>
                    		{!! nl2br($value->content); !!}
	                    </div>
	                </div>
	                <div class="d-flex fs-14">
		                
	                </div>
	            </div>
	        </div>
	    </label>
	</div>
	@endforeach
@else
	<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
	    <span class="fs-70 mb-3 text-primary">
	        <i class="fa-light fa-quote-right"></i>
	    </span>
	    <div class="fw-semibold fs-5 mb-2 text-gray-900">
	        {{ __('No Captions Yet') }}
	    </div>
	    <div class="text-body-secondary mb-4 text-center max-w-500">
	        {{ __('Start saving your favorite captions to reuse and streamline your content creation process.') }}
	    </div>
	    <a class="btn btn-dark actionItem" href="{{ module_url("update") }}" data-popup="captionModal" data-call-success="Main.ajaxScroll(true);">
	        <i class="fa-light fa-plus me-1"></i> {{ __('Add new caption') }}
	    </a>
	</div>
@endif