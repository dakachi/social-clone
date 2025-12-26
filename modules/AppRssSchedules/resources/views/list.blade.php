@foreach($schedules as $value)
<div class="mb-4">
	<div class="card border-gray-300">
		<div class="card-body">
			<div class="d-flex flex-column flex-md-row gap-16">
			    <div class="size-80 border b-r-10 fs-40 d-flex align-items-center justify-content-center">
			    	<i class="fa-light fa-square-rss text-warning"></i>
			    </div>
			    <div class="flex-grow-1 d-flex flex-column">
			    	<div class="mb-2">
			    		<div class="fw-5 fs-14 text-truncate-1">{{ $value->title }}</div>
			    		<div class="fw-5 fs-12 text-gray-600 text-truncate-1">{{ $value->desc }}</div>
			    	</div>
			    	<div class="fw-5 fs-12 d-flex align-items-center gap-8 mt-auto">
		    			@if($value->status == 1)
	    				<div class="text-primary">
		    				<i class="fa-light fa-spin fa-loader"></i>
		    				{{ __("Running") }}
		    			</div>
		    			@else
		    			<div class="text-warning">
		    				<i class="fa-light fa-pause"></i>
		    				{{ __("Pause/Stop") }}
		    			</div>
		    			@endif

			    		<div class="text-gray-200 fs-12">|</div>

			    		<div>
			    			<i class="fa-light fa-circle-check text-success me-1"></i>
			    			<span class="text-gray-700">
			    				<span>{{ $value->success }}</span>
			    				<span>{{ __("Published") }}</span>
			    			</span>
			    		</div>

			    		<div class="text-gray-200 fs-12">|</div>

			    		<div>
			    			<i class="fa-light fa-circle-exclamation text-danger me-1"></i>
			    			<span class="text-gray-700">
			    				<span>{{ $value->failed }}</span>
			    				<span>{{ __("Failed") }}</span>
			    			</span>
			    		</div>
			    	</div>
			    </div>
			    <div class="d-flex align-items-center justify-content-start justify-content-md-center gap-16">

			    	<a href="{{ route("app.rss-schedules.edit", ["id" => $value->id_secure]) }}" data-id="{{ $value->id_secure }}" class="fs-18 text-gray-900">
			    		<i class="fa-light fa-gear"></i>
			    	</a>

			    	<a href="{{ route("app.publishing.index", ["query" => 1]) }}" class="fs-18 text-gray-900">
			    		<i class="fa-light fa-clock-rotate-left"></i>
			    	</a>

			    	<div class="btn-group position-static">
                        <a href="javascript:void(0);" class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-18" data-bs-toggle="dropdown" aria-expanded="true">
                            <i class="fa-light fa-grid-2"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 wp-100 max-w-125">
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("status/start") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true);">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-play"></i></span>
                                    <span >{{ __('Start') }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("status/pause") }}" data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true);">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pause"></i></span>
                                    <span>{{ __('Pause/Stop') }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14" href="{{$value->url }}" target="_blank">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-arrow-up-right-from-square"></i></span>
                                    <span>{{ __('RSS URL') }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("destroy") }}"  data-id="{{ $value->id_secure }}" data-call-success="Main.ajaxScroll(true);">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __('Delete') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>

			    </div>
			</div>
		</div>
	</div>
</div>
@endforeach

@if( $schedules->Total() == 0 && $schedules->currentPage() == 1)
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <span class="fs-70 mb-3 text-primary">
        <i class="fa-light fa-square-rss"></i>
    </span>
    <div class="fw-semibold fs-5 mb-2 text-gray-800">
        {{ __('No RSS Schedules yet') }}
    </div>
    <div class="text-body-secondary mb-4">
        {{ __('Start by adding a new RSS feed schedule to automate your content posting.') }}
    </div>
    <a href="{{ route('app.rss-schedules.create') }}" class="btn btn-dark">
        <i class="fa-light fa-plus me-1"></i> {{ __('Add RSS Schedule') }}
    </a>
</div>
@endif
