@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Tiktok API') }}" 
        description="{{ __('Easy Configuration Steps for Tiktok API') }}"
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
                                    <input class="form-check-input" type="radio" name="tiktok_status" value="1" id="tiktok_status_1" {{ get_option("tiktok_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="tiktok_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tiktok_status" value="0" id="tiktok_status_0"{{ get_option("tiktok_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="threads_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Mode') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tiktok_mode" value="1" id="tiktok_mode_1" {{ get_option("tiktok_mode", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="tiktok_mode_1">
                                        {{ __('Live') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tiktok_mode" value="0" id="tiktok_mode_0"{{ get_option("tiktok_mode", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="tiktok_mode_0">
                                        {{ __('Sandbox') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App ID') }}</label>
                            <input class="form-control" name="tiktok_app_id" id="tiktok_app_id" type="text" value="{{ get_option("tiktok_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('App Secret') }}</label>
                            <input class="form-control" name="tiktok_app_secret" id="tiktok_app_secret" type="text" value="{{ get_option("tiktok_app_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Scopes') }}</label>
                            <input class="form-control" name="tiktok_scopes" id="tiktok_scopes" type="text" value="{{ get_option("tiktok_scopes", "user.info.basic,user.info.profile,user.info.stats,video.list,video.publish") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL: ") }} 
                            <a href="{{ url_app("tiktok/profile") }}" target="_blank">{{ url_app("tiktok/profile") }}</a>
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
