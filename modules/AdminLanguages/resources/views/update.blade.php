@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ $result ? __('Edit Language') : __('Add New Language') }}" 
        description="{{ $result ? __('Modify existing language details and settings.') : __('Add a new language to your system.') }}" 
    >
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-800">
    <form class="actionForm" class="mb-4 pb-4" action="{{ module_url("save") }}" data-redirect="{{ module_url() }}">
    	<input class="d-none" name="id" type="text" value="{{ data($result, "id_secure") }}">
		<div class="card border-1 border-gray-300 shadow-none mb-3">
         	<div class="card-body">
         		<div class="msg-errors"></div>
 				<div class="row">
 					<div class="col-md-12">
 						<div class="mb-4">
		                  	<label class="form-label">{{ __('Status') }}</label>
		                  	<div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="1" id="status_1" @checked(($result->status ?? 1) == 1)>
				                  	<label class="form-check-label mt-1" for="status_1">
				                    	{{ __('Enable') }}
				                  	</label>
				                </div>
				                <div class="form-check me-3">
				                  	<input class="form-check-input" type="radio" name="status" value="0" id="status_0" @checked(($result->status ?? 1) == 0)>
				                  	<label class="form-check-label mt-1" for="status_0">
				                    	{{ __('Disable') }}
				                  	</label>
				                </div>
				            </div>
		                </div>
 					</div>
 					<div class="col-md-6">
 						<div class="mb-4">
		                  	<label for="name" class="form-label">{{ __('Name') }}</label>
	                     	<input class="form-control" name="name" id="name" type="text" value="{{ $result->name ?? '' }}">
		                </div>
 					</div>
 					<div class="col-md-6">
 						<div class="mb-4">
		                  	<label for="code" class="form-label">{{ __('Language') }}</label>
		                  	<select class="form-select" name="code" id="code">
                                <option value="">{{ __('Select language') }}</option>
                                @foreach( get_language_codes() as $key => $value )
                                    <option value="{{ $key }}" @selected(($result->code ?? '') == $key)>{{ $value }}</option>
                                @endforeach
                            </select>
		                </div>
 					</div>
 					<div class="col-md-6">
                        <div class="mb-4">
                            <label for="dir" class="form-label">{{ __('Text direction') }}</label>
                            <select class="form-select" name="dir">
                                <option value="ltr" @selected(($result->dir ?? 'ltr') == 'ltr')>{{ __("LTR") }}</option>
                                <option value="rtl" @selected(($result->dir ?? 'ltr') == 'rtl')>{{ __("RTL") }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="is_default" class="form-label">{{ __('Is default') }}</label>
                            <select class="form-select" name="is_default">
                                <option value="1" @selected(($result->is_default ?? 0) == 1)>{{ __("Yes") }}</option>
                                <option value="0" @selected(($result->is_default ?? 0) == 0)>{{ __("No") }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="auto_translate" class="form-label mb-1">{{ __('Auto Translate') }}</label>
                            <div class="text-gray-600 fs-12 mb-2">{{ __("The system will automatically translate the entire language using Google Translate.") }}</div>
                            <select class="form-select" name="auto_translate">
                                <option value="1" @selected(($result->auto_translate ?? 0) == 1)>{{ __("Yes") }}</option>
                                <option value="0" @selected(($result->auto_translate ?? 0) == 0)>{{ __("No") }}</option>
                            </select>
                        </div>
                    </div>
 					<div class="col-md-12">
 						<label for="name" class="form-label">{{ __('Flag') }}</label>
			            <div class="input-group mb-3">
			            	<div class="form-control">
		                     	<i class="fa-light fa-magnifying-glass"></i>
		                     	<input placeholder="{{ __("Search") }}" type="text" class="search-input" value="">
			                </div>
			                <span class="btn btn-icon btn-input min-w-55">
			                    <input class="form-check-input checkbox-all" type="checkbox" value="">
			                </span>
			            </div>
 						<div class="bg-gray-100 pf-0 b-r-4">
		                  	<ul class="list-group overflow-y-scroll max-h-450 border-1 border-gray-300">
		                  		@php
		                  			$flags = glob( Module::getModulePath('AdminLanguages/resources/assets/css/flags/flags')."/*" );
		                  		@endphp

		                  		@foreach($flags as $flag)
		                  			
		                  			@php
		                  				$directory_list = explode("/", $flag);
				                        $flag = end($directory_list);
				                        $ext = explode(".", $flag);
				                        if(count($ext) == 2 && $ext[1] == "svg")
				                            $icon = "flag-icon flag-icon-".$ext[0];
				                    @endphp

			                        <li class="search-list">
			                        	<div class="list-group-item bg-gray-100 border-start-0 border-end-0 d-flex align-items-center gap-8">
									  		<span>
									  			<input class="form-check-input" type="radio" name="icon" value="{{ $icon }}" id="flag_{{ $icon }}" {{ data($result, "icon", "radio", $icon) }}>
									  		</span>
									  		<label  class="mt-1 fs-14" for="flag_{{ $icon }}">
									  			<span class="me-2 ms-2"><i class="{{ $icon }}"></i> </span>
									  			{{ strtoupper($ext[0]) }}
									  		</label>
			                        	</div>
								  	</li>
		                  		@endforeach
							  	
							</ul>
		                </div>
 					</div>
 				</div>

         	</div>
        </div>
     	<div class="mb-3 pb-4">
      		<button type="submit" class="btn btn-dark w-100">
                {{ __('Save changes') }}
            </button>
     	</div>

    </form>

</div>

@endsection

@section('script')
    
@endsection