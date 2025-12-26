@extends('layouts.app')

@section('sub_header')
    <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
        @if( request()->segment(3) == "edit" )
            <div class="d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                    {{ __('Edit Ticket') }}
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Edit and update your support ticket details seamlessly') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                    {{ __('New ticket') }}
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Submit new support requests quickly and easily') }}</span>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="d-flex gap-8">
            <a class="btn btn-light btn-sm " href="{{ module_url() }}">
                <span><i class="fa-light fa-chevron-left"></i></span>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ module_url("save") }}" method="POST">
    	<input class="form-control d-none" name="id" id="id" type="text" value="{{ data($ticket, "id_secure") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                	@if(!$ticket)
                	<div class="col-md-12">
                        <div class="mb-4">
                            <label for="user_id" class="form-label">{{ __('To recipient') }} (<span class="text-danger">*</span>)</label>
                            <select class="form-select" name="user_id" id="user_id" data-control="select2" data-ajax-url="{{ route('admin.users.search') }}" data-selected-id="{{ data($ticket, "id_secure") }}">
                            	<option value="-1">{{ __("Select user") }}</option>
                            </select>
                        </div>
                    </div>
                    @endif
                	<div class="col-md-6">
                        <div class="mb-4">
                            <label for="cate_id" class="form-label">{{ __('Category') }} (<span class="text-danger">*</span>)</label>
                            <select class="form-select" name="cate_id" id="cate_id" data-control="select2">
                            	<option value="-1">{{ __("Select Category") }}</option>
                            	@if(!empty( $categories )) 
	                                @foreach($categories as $value)
	                                <option value="{{ $value->id_secure }}" {{ data($ticket, "cate_id", "select", $value->id) }}>{{ $value->name }}</option>
	                                @endforeach
	                            @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="type_id" class="form-label">{{ __('Type') }}</label>
                            <select class="form-select" name="type_id" id="type_id" data-control="select2">
                            	<option value="-1">{{ __("Select Type") }}</option>
                            	@if(!empty( $types )) 
	                                @foreach($types as $value)
	                                <option value="{{ $value->id_secure }}" {{ data($ticket, "type_id", "select", $value->id) }}>{{ $value->name }}</option>
	                                @endforeach
	                            @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="subject" class="form-label">{{ __('Subject') }} (<span class="text-danger">*</span>)</label>
                            <input class="form-control" name="subject" id="subject" type="text" value="{{ data($ticket, "title") }}" placeholder="{{ __("Enter the subject of your request here") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Content') }} (<span class="text-danger">*</span>)</label>
                            <textarea class="textarea_editor border-gray-300 border-1" name="content" placeholder="{{ __("Describe your issue or request in detail") }}">{!! data($ticket, "content") !!}</textarea>
                        </div>
                    </div>

                    @php 
                    $label_ids = [];
                    if(!empty($ticket) && isset($ticket->label_ids) && !empty($ticket->label_ids)){
                    	$label_ids = $ticket->label_ids;
                    }

                    @endphp
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="labels" class="form-label">{{ __('Labels') }}</label>
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Organize and manage your support tickets easily.") }}</div>
	                        <select class="form-select h-auto" data-control="select2" name="labels[]" multiple="true" data-placeholder="{{ __("Add labels") }}">
	                            @if(!empty( $labels )) 
	                                @foreach($labels as $value)
	                                <option value="{{ $value->id_secure }}" data-icon="{{ $value->icon }} text-{{ $value->color }}" {{ (!empty($label_ids) && in_array($value->id, $label_ids))?"selected":"" }} >{{ $value->name }}</option>
	                                @endforeach
	                            @endif
	                        </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>

    </form>

</div>
@endsection

@section('script')
@endsection