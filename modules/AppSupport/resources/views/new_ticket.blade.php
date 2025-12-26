@extends('layouts.app')

@section('content')

<div class="container max-w-800 pb-5">

    <div class="mt-4 mb-4">
        <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
            <div class="my-3 d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                    {{ __('New ticket') }}
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Submit new support requests quickly and easily') }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-8">
                <a class="btn btn-light btn-sm " href="{{ module_url() }}">
                    <span><i class="fa-light fa-chevron-left"></i></span>
                    <span>{{ __('Back') }}</span>
                </a>
            </div>
        </div>
    </div>

    <form class="actionForm" action="{{ module_url("save") }}" method="POST">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                	<div class="col-md-6">
                        <div class="mb-4">
                            <label for="cate_id" class="form-label">{{ __('Category') }} (<span class="text-danger">*</span>)</label>
                            <select class="form-select" name="cate_id" id="cate_id" data-control="select2">
                            	<option value="-1">{{ __("Select Category") }}</option>
                            	@if(!empty( $categories ))
	                                @foreach($categories as $value)
	                                <option value="{{ $value->id_secure }}">{{ $value->name }}</option>
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
	                                <option value="{{ $value->id_secure }}">{{ $value->name }}</option>
	                                @endforeach
	                            @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="subject" class="form-label">{{ __('Subject') }} (<span class="text-danger">*</span>)</label>
                            <input class="form-control" name="subject" id="subject" type="text" value="" placeholder="{{ __("Enter the subject of your request here") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Content') }} (<span class="text-danger">*</span>)</label>
                            <textarea class="textarea_editor border-1 border-gray-300" name="content" placeholder="{{ __("Describe your issue or request in detail") }}"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="labels" class="form-label">{{ __('Labels') }}</label>
                            <div class="text-gray-600 fs-12 mb-2">{{ __("Organize and manage your support tickets easily.") }}</div>
	                        <select class="form-select h-auto" data-control="select2" name="labels[]" multiple="true" data-placeholder="{{ __("Add labels") }}">
	                            @if(!empty( $labels ))
	                                @foreach($labels as $value)
	                                <option value="{{ $value->id_secure }}" data-icon="{{ $value->icon }} text-{{ $value->color }}">{{ $value->name }}</option>
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
