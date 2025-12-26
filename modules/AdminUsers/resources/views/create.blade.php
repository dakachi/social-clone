@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Add new user') }}" 
        description="{{ __('Register new user and set permissions instantly.') }}" 
    >
        <a class="btn btn-primary btn-sm d-flex align-items-center justify-content-between" href="{{ url_admin("users") }}">
        	<span><i class="fa-light fa-list"></i></span>
        	<span>
        		{{ __('User list') }}
        	</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    
<div class="container">
	
    <form class="actionForm" action="{{ url_admin("users/save") }}" data-redirect="{{ url_admin("users") }}">
    	
    	<div class="mb-5">
    		<div class="card mt-5">
             	<div class="card-header">
              		<h3 class="card-title">
               			{{ __('User information') }}
              		</h3>
             	</div>
             	<div class="card-body">
             		<div class="d-flex flex-column flex-lg-row flex-md-column gap-32">
             			<div class="max-w-220 min-w-220">
             				<div class="p-3 border-1 b-r-6">
             					@include('appfiles::block_upload', [
                                    "large" => true,
                                    "id" => "avatar",
                                    "name" => __("Upload Avatar"),
                                ])
             				</div>
             			</div>
             			<div class="flex-fill">
             				<div class="row">
             					<div class="col-md-12">
             						<div class="mb-4">
					                  	<label class="form-label">{{ __('Role') }}</label>
					                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
							                <div class="form-check me-3">
							                  	<input class="form-check-input" type="radio" name="role" value="1" checked="">
							                  	<label class="form-check-label mt-1">
							                    	{{ __('User') }}
							                  	</label>
							                </div>
							                <div class="form-check me-3">
							                  	<input class="form-check-input" type="radio" name="role" value="2" >
							                  	<label class="form-check-label mt-1">
							                    	{{ __('Admin') }}
							                  	</label>
							                </div>
							            </div>
					                </div>
             					</div>
             					<div class="col-md-12">
             						<div class="mb-4">
					                  	<label class="form-label">{{ __('Status') }}</label>
					                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
							                <div class="form-check me-3">
							                  	<input class="form-check-input" type="radio" name="status" value="2" id="status_2" checked="">
							                  	<label class="form-check-label mt-1">
							                    	{{ __('Active') }}
							                  	</label>
							                </div>
							                <div class="form-check me-3">
							                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1">
							                  	<label class="form-check-label mt-1">
							                    	{{ __('Inactive') }}
							                  	</label>
							                </div>
							                <div class="form-check me-3">
							                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0">
							                  	<label class="form-check-label mt-1">
							                    	{{ __('Banned') }}
							                  	</label>
							                </div>
							            </div>
					                </div>
             					</div>
             					<div class="col-md-6">
             						<div class="mb-4">
					                  	<label for="fullname" class="form-label">{{ __('Fullname') }}</label>
					                  	<div class="form-control">
					                     	<i class="fa-light fa-user"></i>
					                     	<input placeholder="{{ __('Fullname') }}" name="fullname" id="fullname" type="text" value="">
						                </div>
					                </div>
             					</div>
             					<div class="col-md-6">
             						<div class="mb-4">
					                  	<label for="username" class="form-label">{{ __('Username') }}</label>
					                  	<div class="form-control">
					                     	<i class="fa-light fa-user"></i>
					                     	<input placeholder="{{ __('Username') }}" name="username" id="username" type="text" value="">
						                </div>
					                </div>
             					</div>
             					<div class="col-md-6">
             						<div class="mb-4">
					                  	<label for="email" class="form-label">{{ __('Email') }}</label>
					                  	<div class="form-control">
					                     	<i class="fa-light fa-envelope"></i>
					                     	<input placeholder="{{ __('Email') }}" name="email" id="email" type="text" value="">
						                </div>
					                </div>
             					</div>

             					<div class="col-md-6">
             						<div class="mb-4">
             							<label for="timezone" class="form-label">{{ __('Timezone') }}</label>
             							<div class="form-control pe-0">
					                     	<i class="fa-light fa-clock"></i>
						                	<select class="form-select" name="timezone" id="timezone">
						                		<option value="">{{ __('Select timezone') }}</option>
						                		@foreach( tz_list() as $key => $value )
						                			<option value="{{ $key }}">{{ $value }}</option>
						                		@endforeach
							                </select>
						                </div>
					                </div>
             					</div>

             					<div class="col-12"></div>

             					<div class="d-flex pb-3">
				                    <div class="fs-18 text-gray-900 fw-5">{{ __('Password') }}</div>  
				                </div>

             					<div class="col-md-6">
             						<div class="mb-4">
             							<label for="password" class="form-label">{{ __('Password') }}</label>
             							<div class="form-control">
					                     	<i class="fa-light fa-key"></i>
					                     	<input placeholder="{{ __('Password') }}" name="password" id="password" type="password" autocomplete="on" value="">
						                </div>
					                </div>
             					</div>
             					<div class="col-md-6">
             						<div class="mb-4">
					                  	<label for="password_confirmation" class="form-label">{{ __('Confirm password') }}</label>
					                  	<div class="form-control">
					                     	<i class="fa-light fa-key"></i>
					                     	<input placeholder="{{ __('Confirm password') }}" name="password_confirmation" id="password_confirmation" type="password" autocomplete="on" value="">
						                </div>
					                </div>
             					</div>

             					<div class="col-12"></div>

             					<div class="d-flex pb-3">
				                    <div class="fs-18 text-gray-900 fw-5">{{ __('Plan') }}</div>  
				                </div>

             					<div class="col-md-6">
             						<div class="mb-4">
             							<label for="plan" class="form-label">{{ __('Plan') }}</label>
             							<div class="text-gray-600 fs-12 mb-2">{{ __('All previous permissions will be removed when you switch to a new plan.') }}</div>
             							<div class="form-control pe-0">
					                     	<i class="fa-light fa-cubes"></i>
						                	<select class="form-select" name="plan">
												<option value="0">{{ __('Select plan') }}</option>
												@if($plans)
													@foreach($plans as $plan)

														@php

															switch ($plan->type) {
																case 2:
																	$type = __("Yearly");
																	break;

																case 3:
																	$type = __("Lifetime");
																	break;
																
																default:
																	$type = __("Monthly");
																	break;
															}

														@endphp

														<option value="{{ $plan->id }}">[{{ $type }}] {{ $plan->name }}</option>
													@endforeach
												@endif
							                </select>
						                </div>
					                </div>
             					</div>
             					<div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="expiration_date" class="form-label mb-1">{{ __('Expiration date') }}</label>
                                        <div class="text-gray-600 fs-12 mb-2">{{ __('Set the value to -1 for unlimited') }}</div>
                                        <div class="input-group">
                                            <div class="form-control">
                                                <i class="fa-light fa-calendar-clock"></i>
                                                <input placeholder="{{ __('Expiration date') }}" name="expiration_date" class="dateBtn" id="expiration_date" type="text">
                                                
                                            </div>
                                            <button type="button" class="btn btn-icon btn-light selectDate"><i class="fa-light fa-calendar-days"></i></button>
                                        </div>
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
		</div>

    </form>

</div>

@endsection

@section('script')
    
@endsection
