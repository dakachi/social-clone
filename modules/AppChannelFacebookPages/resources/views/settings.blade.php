@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Facebook API') }}" 
        description="{{ __('Easy Configuration Steps for Facebook API') }}" 
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
                            <input class="form-control" name="facebook_app_id" id="facebook_app_id" type="text" value="{{ get_option("facebook_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App Secret') }}</label>
                            <input class="form-control" name="facebook_app_secret" id="facebook_app_secret" type="text" value="{{ get_option("facebook_app_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Graph version') }}</label>
                            <input class="form-control" name="facebook_graph_version" id="facebook_graph_version" type="text" value="{{ get_option("facebook_graph_version", "v22.0") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Facebook page") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="facebook_page_status" value="1" id="facebook_page_status_1" {{ get_option("facebook_page_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="facebook_page_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="facebook_page_status" value="0" id="facebook_page_status_0"{{ get_option("facebook_page_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="facebook_page_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Permissions') }}</label>
                            <input class="form-control" name="facebook_page_permissions" id="facebook_page_permissions" type="text" value="{{ get_option("facebook_page_permissions", "pages_read_engagement,pages_manage_posts,pages_show_list,business_management") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL: ") }} 
                            <a href="{{ url_app("facebook/page") }}" target="_blank">{{ url_app("facebook/page") }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Facebook profile") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="facebook_profile_status" value="1" id="facebook_profile_status_1" {{ get_option("facebook_profile_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="facebook_profile_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="facebook_profile_status" value="0" id="facebook_profile_status_0"{{ get_option("facebook_profile_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="facebook_profile_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Permissions') }}</label>
                            <input class="form-control" name="facebook_profile_permissions" id="facebook_profile_permissions" type="text" value="{{ get_option("facebook_profile_permissions", "public_profile,publish_video") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL: ") }} 
                            <a href="{{ url_app("facebook/profile") }}" target="_blank">{{ url_app("facebook/profile") }}</a>
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
