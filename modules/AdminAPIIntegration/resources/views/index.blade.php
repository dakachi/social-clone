@extends('adminapiintegration::layouts.master')

@section('sub_header')
    <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
        <div class="d-flex flex-column gap-8">
            <h1 class="fs-20 font-medium lh-1 text-gray-900">
                <span>{{ __("Admin API Integration") }}</span> 
            </h1>
            <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                <div class="d-flex gap-8">
                    <span class="text-gray-600"><span class="text-gray-600">{{ __('Seamless integration for managing admin API requests') }}</span></span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-8">
            <a class="btn btn-dark btn-sm" href="{{ module_url("new-ticket") }}">
                <span><i class="fa-light fa-plus"></i></span>
                <span>{{ __('Create new') }}</span>
            </a>
        </div>
    </div>
@endsection


@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('adminapiintegration.name') !!}</p>
@endsection
