@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Instagram API') }}" 
        description="{{ __('Easy Configuration Steps for Instagram API') }}" 
    >
    </x-sub-header>
@endsection

@section('content')
<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="instagram_status" value="1" id="instagram_status_1" {{ get_option("instagram_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="instagram_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="instagram_status" value="0" id="instagram_status_0"{{ get_option("instagram_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="instagram_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App ID') }}</label>
                            <input class="form-control" name="instagram_app_id" id="instagram_app_id" type="text" value="{{ get_option("instagram_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App Secret') }}</label>
                            <input class="form-control" name="instagram_app_secret" id="instagram_app_secret" type="text" value="{{ get_option("instagram_app_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Graph version') }}</label>
                            <input class="form-control" name="instagram_graph_version" id="instagram_graph_version" type="text" value="{{ get_option("instagram_graph_version", "v21.0") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Permissions') }}</label>
                            <input class="form-control" name="instagram_permissions" id="instagram_permissions" type="text" value="{{ get_option("instagram_permissions", "instagram_basic,instagram_content_publish,pages_read_engagement,pages_show_list,instagram_manage_insights,business_management") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL: ") }} 
                            <a href="{{ url_app("instagram/profile") }}" target="_blank">{{ url_app("instagram/profile") }}</a>
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
