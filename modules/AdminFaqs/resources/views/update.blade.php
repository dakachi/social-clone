@extends('layouts.app')

@section('sub_header')
    <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
        @if( request()->segment(3) == "edit" )
            <div class="d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                    {{ __('Edit FAQ') }}
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Modify and update your FAQ with ease') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex flex-column gap-8">
                <h1 class="fs-20 font-medium lh-1 fw-6 text-gray-900">
                    {{ __('New FAQ') }}
                </h1>
                <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                    <div class="d-flex gap-8">
                        <span class="text-gray-600">{{ __('Craft and publish a new FAQ effortlessly') }}</span>
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
    <form class="actionForm" action="{{ module_url("save") }}" method="POST" data-redirect="{{ module_url("") }}">
        <input class="form-control d-none" name="id" id="id" type="text" value="{{ data($result, "id_secure") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-5">
                    {{ __("FAQ Detail") }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                	<div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="status" value="1" id="status_1" {{ data($result, "status", "radio", 1, 1) }} >
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
                            <label for="title" class="form-label">{{ __('Title') }} (<span class="text-danger">*</span>)</label>
                            <input class="form-control" name="title" id="title" type="text" value="{{ data($result, "title") }}" placeholder="{{ __("Enter title") }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Content') }} (<span class="text-danger">*</span>)</label>
                            <textarea class="textarea_editor border-gray-300 border-1 min-h-300" name="content" placeholder="{{ __("Enter content") }}">{!! data($result, "content") !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12">
            <button type="submit" class="btn btn-dark b-r-10 w-100">
                {{ __('Save changes') }}
            </button>
        </div>

    </form>

</div>
@endsection
