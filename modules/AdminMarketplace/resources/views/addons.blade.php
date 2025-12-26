@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Manage Addons') }}" 
        description="{{ __('Discover and install powerful modules for Stackposts') }}" 
    >
        <div class="d-flex gap-8">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <div class="form-control form-control-sm">
                        <span class="btn btn-icon">
                            <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="ajax-scroll-filter" name="search" placeholder="{{ __('Enter your keyword') }}" type="text">
                    </div>
                    <button type="submit" class="btn btn-sm btn-light">
                        {{ __("Search") }}
                    </button>
                </div>
            </form>
            <a class="btn btn-outline btn-success btn-sm text-nowrap" href="{{ module_url("") }}">
                <span><i class="fa-light fa-store"></i></span>
                <span>{{ __('Marketplace') }}</span>
            </a>
            <a class="btn btn-dark btn-sm actionItem" href="{{ module_url("install") }}" data-popup="installModal">
                <span><i class="fa-light fa-file-zipper"></i></span>
                <span>{{ __('Install') }}</span>
            </a>
        </div>
    </x-sub-header>
@endsection

@section('content')
<div class="container py-5 marketplace-wrapper max-w-850">
    
	    <div class="d-flex flex-column gap-3">
        @forelse($addons as $addon)
        	<div class="card hp-100 d-flex flex-column rounded-4 overflow-hidden card border-0 shadow-sm rounded-4 mb-4">
    			<div class="card-body  px-4 py-4 gap-16 align-items-center flex-grow-1">
    				<div class="d-flex flex-column flex-md-row flex-grow-1 gap-16 align-items-top">
	        			<div class="size-60 d-flex justify-content-center align-items-center position-relative">
	    					@if($addon->thumbnail)
	    						<img src="{{ $addon->thumbnail }}" class="wp-100 hp-100 b-r-10 position-relative zIndex-1">
	    					@else
		    					<div class="size-60 border-2 border-gray-200 b-r-10 position-relative d-flex justify-content-center align-items-center">
		    						<i class="{{ $addon->icon }} fs-30" style="color: {{ $addon->color  }}" class="position-relative zIndex-1"></i>
		    						<div class="position-absolute wp-100 hp-100 opacity-25 b-r-10 t-0" style="background: {{ $addon->color  }};"></div>
		    					</div>
	    					@endif
	        			</div>

	        			<div class="flex-grow-1">
	    					<div class="d-flex justify-content-between gap-3">
	    						<div class="fw-bold fs-18 mb-0">
	    							{{ $addon->name }}
	    						</div>
	    						<div>
	    							@if($addon->status === 1)
	    								<span class="badge badge-outline badge-success b-r-6">{{ __('Activated') }}</span>
	    							@else
	    								<span class="badge badge-outline badge-danger b-r-6">{{ __('Deactivated') }}</span>
	    							@endif
	    						</div>
	    					</div>
	    					<div class="fs-12 mb-1 text-gray-600 text-truncate-1">
	    						@if($addon->version)
	    							{{ __("Version:") }} {{ $addon->version }}
	    						@endif
	    					</div>
	    					<div class="flex-grow-1 fs-14 mb-0 text-gray-600">
	    						<div class="text-truncate-2">
	    							{{ $addon->description }}
	    						</div>
	    					</div>
		        			<div class="d-flex gap-8 mt-3">
		        				@if($addon->has_update)
		        				<a href="{{ route("admin.marketplace.do_update", $addon->product_id??'') }}" class="btn rounded-3 btn-warning actionItem" data-redirect="" data-bs-title="{{ __("Update Add-on") }}" data-bs-toggle="tooltip" data-bs-placement="top">
					             	<i class="fa-light fa-circle-arrow-up"></i> 
					             	{{ __('Update to :version', ['version' => $addon->latest_version]) }}
					            </a>
		        				@endif
		        				@if($addon->uri)
		        				<a href="{{ url($addon->uri) }}" class="btn rounded-3 btn-icon btn-secondary" data-bs-title="{{ __("Go to Add-on") }}" data-bs-toggle="tooltip" data-bs-placement="top">
		        					<i class="fa-light fa-arrow-up-right-from-square"></i>
		        				</a>
		        				@endif
		        				@if(!$addon->is_main)
						            @if($addon->status === 0)
			        				<a href="{{ route("admin.marketplace.active", $addon->id_secure ?? '') }}" class="btn rounded-3 btn-icon btn-outline btn-success actionItem" data-redirect="" data-bs-title="{{ __("Active Add-on") }}" data-bs-toggle="tooltip" data-bs-placement="top">
						             	<i class="fa-light fa-plug-circle-plus"></i>
						            </button>
						            @else
						            <a href="{{ route("admin.marketplace.deactive", $addon->id_secure ?? '') }}" class="btn rounded-3 btn-icon btn-outline btn-danger actionItem" data-confirm="{{ __("Are you sure you want to deactive this addon?") }}" data-redirect="" data-bs-title="{{ __("Deactive Add-on") }}" data-bs-toggle="tooltip" data-bs-placement="top">
						             	<i class="fa-light fa-power-off"></i>
						            </button>
						            @endif
			        				<a href="{{ route("admin.marketplace.destroy", $addon->id_secure ?? '') }}" class="btn rounded-3 btn-icon btn-light actionItem" data-confirm="{{ __("The addon will be permanently deleted from the system and cannot be recovered. Are you sure you want to delete this addon?") }}" data-redirect="" data-bs-title="{{ __("Delete Add-on") }}" data-bs-toggle="tooltip" data-bs-placement="top">
						             	<i class="fa-light fa-trash-can"></i>
						            </a>
				             	@endif
	        			</div>
	        			</div>
        			</div>

        		</div>
        	</div>
        @empty
            <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
			    <span class="fs-70 mb-3 text-primary">
			        <i class="fa-light fa-plug"></i>
			    </span>
			    <div class="fw-semibold fs-5 mb-2 text-gray-800">
			        {{ __('No addons found') }}
			    </div>
			    <div class="text-body-secondary mb-4">
			        {{ __('There are currently no addons available in the system.') }}
			    </div>
			</div>
        @endforelse
    </div>

    @if($addons->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $addons->links('components.pagination') }}
    </div>
    @endif


</div>
@endsection
