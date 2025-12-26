@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Linkedin API') }}" 
        description="{{ __('Easy Configuration Steps for Linkedin API') }}"
    >
    </x-sub-header>
@endsection

@section('content') 
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("General configuration") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App ID') }}</label>
                            <input class="form-control" name="linkedin_app_id" id="linkedin_app_id" type="text" value="{{ get_option("linkedin_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App Secret') }}</label>
                            <input class="form-control" name="linkedin_app_secret" id="linkedin_app_secret" type="text" value="{{ get_option("linkedin_app_secret", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Linkedin profile") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="linkedin_profile_status" value="1" id="linkedin_profile_status_1" {{ get_option("linkedin_profile_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="linkedin_profile_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="linkedin_profile_status" value="0" id="linkedin_profile_status_0"{{ get_option("linkedin_profile_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="linkedin_profile_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            <span class="fw-5">{{ __("Callback URL: ") }}</span>
                            <a href="{{ url_app("linkedin/profile") }}" target="_blank">{{ url_app("linkedin/profile") }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Linkedin page") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="linkedin_page_status" value="1" id="linkedin_page_status_1" {{ get_option("linkedin_page_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="linkedin_page_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="linkedin_page_status" value="0" id="linkedin_page_status_0"{{ get_option("linkedin_page_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="linkedin_page_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            <span class="fw-5">{{ __("Callback URL: ") }}</span>
                            <a href="{{ url_app("linkedin/page") }}" target="_blank">{{ url_app("linkedin/page") }}</a>
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
