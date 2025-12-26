@extends('layouts.app')


@section('sub_header')
    <x-sub-header 
        title="{{ $result ? __('Edit Coupon') : __('Create New Coupon') }}" 
        description="{{ $result ? __('Modify existing subscription plan details and pricing options.') : __('Easily create and customize a new subscription plan.') }}" 
    >
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-700 pb-4">
    <form class="actionForm" action="{{ module_url("save") }}" data-redirect="{{ module_url() }}">
    	<input class="d-none" name="id" type="text" value="{{ $result->id_secure ?? '' }}">
		<div class="card border-gray-300 b-r-6 mt-5 mb-3">
         	<div class="card-body">
 				<div class="row">
 					<div class="col-md-6">
 						<div class="mb-4">
		                  	<label class="form-label">{{ __('Status') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1" @checked( ($result->stauts ?? 1) == 1 )>
				                  	<label class="form-check-label mt-1" for="status_1">
				                    	{{ __('Enable') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" @checked( ($result->stauts ?? 1) == 0 )>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Disable') }}
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 					<div class="col-md-6">
 						<div class="mb-4">
		                  	<label class="form-label">{{ __('Coupon by') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="type" value="1" id="type_1" @checked( ($result->type ?? 1) == 1 )>
				                  	<label class="form-check-label mt-1" for="type_1">
				                    	{{ __('Percent') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="type" value="2" id="type_2" @checked( ($result->type ?? 1) == 2 )>
				                  	<label class="form-check-label mt-1" for="type_2">
				                    	{{ __('Price') }}
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input class="form-control" name="name" id="name" type="text" value="{{ $result->name ?? '' }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="code" class="form-label">{{ __('Code') }}</label>
	                     	<input class="form-control" name="code" id="code" type="text" value="{{ $result->code ?? '' }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="discount" class="form-label">{{ __('Discount value') }}</label>
	                     	<input class="form-control" name="discount" id="discount" type="number" value="{{ $result->discount ?? '' }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="start_date" class="form-label">{{ __('Start date') }}</label>
	                     	<input class="form-control datetime" name="start_date" id="start_date" type="text" value="{{ datetime_show($result->start_date ?? '')  }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="end_date" class="form-label">{{ __('End date') }}</label>
	                     	<input class="form-control datetime" name="end_date" id="end_date" type="text" value="{{ datetime_show($result->end_date ?? '') }}">
		                </div>
 					</div>
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label for="usage_limit" class="form-label">{{ __('Usage limit') }}</label>
	                     	<input class="form-control" name="usage_limit" id="usage_limit" type="number" value="{{ $result->usage_limit ?? '' }}">
		                  	<div class="text-gray-600 fs-11 mt-1">{{ __('Set -1 is unlimited') }}</div>
		                </div>
 					</div>

 					<div class="col-md-12">
						<label for="name" class="form-label">{{ __('Allow for plans') }}</label>
			            <div class="input-group mb-3">
			            	<div class="form-control">
		                     	<i class="fa-light fa-magnifying-glass"></i>
		                     	<input placeholder="{{ __("Search") }}" type="text" class="search-input" value="">
			                </div>
			                <span class="btn btn-icon btn-input min-w-55">
			                    <input class="form-check-input checkbox-all" type="checkbox" value="">
			                </span>
			            </div>
 						<div class="mb-4 pf-0 b-r-4">
 							@if($plans)
	 							@php

	 								$selected_plans = [];
	 								if($result){
	 									$selected_plans = json_decode( $result->plans );
	 								}

	 							@endphp

			                  	<ul class="list-group border overflow-y-scroll max-h-250">
			                  		@foreach($plans as $value)
			                  			<li class="search-list">
					                        <div class="list-group-item border-start-0 border-end-0 border-top-0 d-flex justify-content-between align-items-center gap-8">
										  		<label  class="mt-1 fs-14 d-flex align-items-center gap-8 text-truncate" for="id_{{ $value->id_secure }}">
										  			<div class="size-35 min-w-35 size-child">
										  				<img src="{{ text2img($value->name); }}" class="border b-r-6">
										  			</div>
										  			<div class="text-truncate">
										  				<div class="fs-12 lh-sm mb-1 fw-5">{{ $value->name }}</div>
										  				<div class="fs-10 lh-sm text-gray-500">{{ __( $value->desc ) }}</div>
										  			</div>
										  		</label>
										  		<span>
										  			<input class="form-check-input checkbox-item" type="checkbox" name="plans[]" value="{{ $value->id_secure }}" id="id_{{ $value->id_secure }}" {{ __( in_array($value->id, $selected_plans)?"checked":"" ) }} >
										  		</span>
										  	</div>
			                  			</li>
			                  		@endforeach
								</ul>
							@endif
		                </div>
 					</div>
 				</div>

         	</div>
        </div>

        <button type="submit" class="btn btn-dark w-100">
            {{ __('Save changes') }}
        </button>

    </form>

</div>

@endsection
