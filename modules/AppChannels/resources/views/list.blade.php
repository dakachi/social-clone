@if( $channels->Total() )

	@foreach( $channels as $key => $value )

	@php
		switch ($value->status) {
			case 1:
				$status_border = "";
				$status_bg = "";
				break;
			
			case 2:
				$status_border = "border-warning";
				$status_bg = "bg-warning-100";
				break;

			default:
				$status_border = "border-danger";
				$status_bg = "bg-danger-100";
				break;
		}
	@endphp

	<div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-4 col-xxl-3 mb-4">
	    <label class="card shadow-none  {{ $status_bg }} {{ $status_border }}" for="channel_{{ $value->id_secure }}">
	        <div class="card-body px-3">
	            <div class="d-flex flex-grow-1 align-items-top gap-8">
	                <div class="text-gray-600 size-40 min-w-40 d-flex align-items-center justify-content-between position-relative">
	                	<a href="{{ $value->url }}" target="_blank" class="text-gray-900 text-hover-primary">
	                		<img data-src="{{ Media::url($value->avatar) }}" src="{{ theme_public_asset('img/default.png') }}" class="b-r-100 w-full h-full border-1 lazyload" onerror="this.src='{{ theme_public_asset('img/default.png') }}'">
		                </a>
	                    <span class="size-17 border-1 b-r-100 position-absolute fs-9 d-flex align-items-center justify-content-between text-center text-white b-0 r-0" style="background-color: {{ $value->module_item['color'] }};">
	                        <div class="w-100"><i class="{{ $value->module_item['icon'] }}"></i></div>
	                    </span>
	                </div>
	                <div class="flex-grow-1 fs-14 fw-5 text-truncate">
	                    <div class="text-truncate">
	                    	<a href="{{ $value->url }}" target="_blank" class="text-gray-900 text-hover-primary">
	                    		{{ $value->name }} {!! $value->login_type!=1?'<span class="text-danger-400 fs-12">'.__("(Unofficial)").'</span>':'' !!}
	                    	</a>
	                    </div>
	                    <div class="fs-12 text-gray-600 text-truncate">
                    		{{ __( ucfirst( $value->social_network." ".$value->category ) ) }}
	                    </div>
	                </div>
	                <div class="d-flex fs-14">
		                <input class="form-check-input checkbox-item" type="checkbox" name="id[]" value="{{ $value->id_secure }}" id="channel_{{ $value->id_secure }}">
	                </div>
	            </div>
	        </div>
	        <div class="card-footer fs-12 d-flex justify-content-center gap-8">
	            <a href="{{ url($value->reconnect_url) }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5">
	                <i class="fa-light fa-arrows-rotate-reverse"></i> 
	                <span>{{ __("Reconnect") }}</span>
	            </a>
	            @if($value->status != 0)
	            <div class="text-gray-400 h-20 w-1 bg-gray-200 "></div>

	            	@if($value->status == 1)
	            	<a href="{{ module_url("status/pause") }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true)">
		                <i class="fa-light fa-pause"></i>
		                <span>{{ __("Pause") }}</span>
		            </a>
		            @else
		            <a href="{{ module_url("status/active") }}" class="d-flex flex-fill gap-8 align-items-center justify-content-center text-gray-900 text-hover-primary fw-5 actionItem" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true)">
		                <i class="fa-light fa-check"></i>
		                <span>{{ __("Active") }}</span>
		            </a>
	            	@endif

		            
	            @endif
	        </div>
	    </label>
	</div>
	@endforeach
@else
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-chart-network"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-800">
        {{ __('No Social Channels Connected') }}
    </div>
    <div class="text-body-secondary mb-4 text-center">
        {{ __('Connect your social channels to manage and track all your accounts in one place.') }}
    </div>
    <a class="btn btn-dark" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addChannelModal">
        <i class="fa-light fa-plus me-1"></i> {{ __('Add Channel') }}
    </a>
</div>
@endif