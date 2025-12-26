@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Login and Authentication Settings') }}"
        description="{{ __('Manage social logins, credentials, and authentication preferences') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("User Onboarding & Preferences") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Landing page') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_landing_page_status" value="1" id="auth_landing_page_status_1" {{ get_option("auth_landing_page_status", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_landing_page_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_landing_page_status" value="0" id="auth_landing_page_status_0"{{ get_option("auth_landing_page_status", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_landing_page_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Signup page') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_signup_page_status" value="1" id="auth_signup_page_status_1" {{ get_option("auth_signup_page_status", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_signup_page_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_signup_page_status" value="0" id="auth_signup_page_status_0"{{ get_option("auth_signup_page_status", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_signup_page_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Activation email to new user') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_activation_email_new_user_status" value="1" id="auth_activation_email_new_user_status_1" {{ get_option("auth_activation_email_new_user_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_activation_email_new_user_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_activation_email_new_user_status" value="0" id="auth_activation_email_new_user_status_0"{{ get_option("auth_activation_email_new_user_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_activation_email_new_user_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Welcome email to new user') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_welcome_email_new_user_status" value="1" id="auth_welcome_email_new_user_status_1" {{ get_option("auth_welcome_email_new_user_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_welcome_email_new_user_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_welcome_email_new_user_status" value="0" id="auth_welcome_email_new_user_status_0"{{ get_option("auth_welcome_email_new_user_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_welcome_email_new_user_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('User can change email') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_user_change_email_status" value="1" id="auth_user_change_email_status_1" {{ get_option("auth_user_change_email_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_user_change_email_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_user_change_email_status" value="0" id="auth_user_change_email_status_0"{{ get_option("auth_user_change_email_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_user_change_email_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('User can change username') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_user_change_username_status" value="1" id="auth_user_change_username_status_1" {{ get_option("auth_user_change_username_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_user_change_username_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_user_change_username_status" value="0" id="auth_user_change_username_status_0"{{ get_option("auth_user_change_username_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_user_change_username_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Facebook login") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_facebook_login_status" value="1" id="auth_facebook_login_status_1" {{ get_option("auth_facebook_login_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_facebook_login_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_facebook_login_status" value="0" id="auth_facebook_login_status_0"{{ get_option("auth_facebook_login_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_facebook_login_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL:") }}<br>
                            <a href="{{ url('auth/login/facebook/callback') }}" target="_blank">
                                {{ url('auth/login/facebook/callback') }}
                            </a>
                            <br><br>
                            {{ __("Click this link to create Facebook app:") }}<br>
                            <a href="https://developers.facebook.com/apps/create/" target="_blank">
                                https://developers.facebook.com/apps/create/
                            </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Facebook App ID') }}</label>
                            <input class="form-control" name="auth_facebook_login_app_id" id="auth_facebook_login_app_id" type="text" value="{{ get_option("auth_facebook_login_app_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Facebook App Secret') }}</label>
                            <input class="form-control" name="auth_facebook_login_app_secret" id="auth_facebook_login_app_secret" type="text" value="{{ get_option("auth_facebook_login_app_secret", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Facebook App Version') }}</label>
                            <input class="form-control" name="auth_facebook_login_app_version" id="auth_facebook_login_app_version" type="text" value="{{ get_option("auth_facebook_login_app_version", "v22.0") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Google login") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_google_login_status" value="1" id="auth_google_login_status_1" {{ get_option("auth_google_login_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_google_login_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_google_login_status" value="0" id="auth_google_login_status_0"{{ get_option("auth_google_login_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_google_login_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL:") }}<br>
                            <a href="{{ url('auth/login/google/callback') }}" target="_blank">
                                {{ url('auth/login/google/callback') }}
                            </a>
                            <br><br>
                            {{ __("Click this link to create Google app:") }}<br>
                            <a href="https://console.cloud.google.com/projectcreate" target="_blank">
                                https://console.cloud.google.com/projectcreate
                            </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Google Client ID') }}</label>
                            <input class="form-control" name="auth_google_login_client_id" id="auth_google_login_client_id" type="text" value="{{ get_option("auth_google_login_client_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Google client Secret') }}</label>
                            <input class="form-control" name="auth_google_login_client_secret" id="auth_google_login_client_secret" type="text" value="{{ get_option("auth_google_login_client_secret", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Twitter login") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_x_login_status" value="1" id="auth_x_login_status_1" {{ get_option("auth_x_login_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_x_login_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_x_login_status" value="0" id="auth_x_login_status_0"{{ get_option("auth_x_login_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_x_login_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-primary fs-14">
                            {{ __("Callback URL:") }}<br>
                            <a href="{{ url('auth/login/x/callback') }}" target="_blank">
                                {{ url('auth/login/x/callback') }}
                            </a>
                            <br><br>
                            {{ __("Click this link to create X (Twitter) app:") }}<br>
                            <a href="https://developer.twitter.com/en/portal/dashboard" target="_blank">
                                https://developer.twitter.com/en/portal/dashboard
                            </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('X Client ID') }}</label>
                            <input class="form-control" name="auth_x_login_client_id" id="auth_x_login_client_id" type="text" value="{{ get_option("auth_x_login_client_id", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('X Client Secret') }}</label>
                            <input class="form-control" name="auth_x_login_client_secret" id="auth_x_login_client_secret" type="text" value="{{ get_option("auth_x_login_client_secret", "") }}">
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
