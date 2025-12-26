@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('CAPTCHA Settings') }}" 
        description="{{ __('Configure bot protection using captcha verification and options') }}" 
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
                            <label for="captcha_type" class="form-label">{{ __('CAPTCHA Type') }}</label>
                            <select class="form-select" name="captcha_type" id="captcha_type">
                                <option value="disable" {{ get_option('captcha_type', 'disable') == 'disable' ? 'selected' : '' }}>{{ __("Disable") }}</option>
                                <option value="recaptcha" {{ get_option('captcha_type', 'disable') == 'recaptcha' ? 'selected' : '' }}>{{ __("Google reCaptcha V2") }}</option>
                                <option value="turnstile" {{ get_option('captcha_type', 'disable') == 'turnstile' ? 'selected' : '' }}>{{ __("Cloudflare Turnstile") }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Google reCaptcha V2") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_google_recaptcha_status" value="1" id="auth_google_recaptcha_status_1" {{ get_option("auth_google_recaptcha_status", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_google_recaptcha_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_google_recaptcha_status" value="0" id="auth_google_recaptcha_status_0"{{ get_option("auth_google_recaptcha_status", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_google_recaptcha_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>                                       
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Google site key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="auth_google_recaptcha_site_key" id="auth_google_recaptcha_site_key" type="text" value="{{ get_option("auth_google_recaptcha_site_key", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Google secret key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="auth_google_recaptcha_secret_key" id="auth_google_recaptcha_secret_key" type="text" value="{{ get_option("auth_google_recaptcha_secret_key", "") }}">
                        </div>
                    </div>                                      
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Cloudflare Turnstile") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_cloudflare_turnstile_status" value="1" id="auth_cloudflare_turnstile_status_1" {{ get_option("auth_cloudflare_turnstile_status_status", 1)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_cloudflare_turnstile_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="auth_cloudflare_turnstile_status" value="0" id="auth_cloudflare_turnstile_status_0"{{ get_option("auth_cloudflare_turnstile_status", 1)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="auth_cloudflare_turnstile_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>                                       
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Cloudflare site key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="auth_cloudflare_turnstile_site_key" id="auth_cloudflare_turnstile_site_key" type="text" value="{{ get_option("auth_cloudflare_turnstile_site_key", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Cloudflare secret key') }}</label>
                            <input placeholder="{{ __('Enter API Key') }}" class="form-control" name="auth_cloudflare_turnstile_secret_key" id="auth_cloudflare_turnstile_secret_key" type="text" value="{{ get_option("auth_cloudflare_turnstile_secret_key", "") }}">
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