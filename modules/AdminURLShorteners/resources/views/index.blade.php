@extends('layouts.app')


@section('sub_header')
    <x-sub-header 
        title="{{ __('URL Shorteners Configuration') }}" 
        description="{{ __('Optimize, manage, and customize shortened link settings') }}" 
    >
    </x-sub-header>
@endsection

@section('content')

@php
    $URLShorteners = URLShortener::getPlatforms();
@endphp

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
                            <label for="name" class="form-label">{{ __('URL Shortener Platform') }}</label>
                            <select class="form-select" name="url_shorteners_platform">
                                    <option value="0" {{ get_option("url_shorteners_platform", 0)==0?"selected":"" }} >{{ __("Disable") }}</option>
                                @foreach($URLShorteners as $key => $value)
                                    <option value="{{ $key }}" {{ get_option("url_shorteners_platform", 0)==$key?"selected":"" }} >{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Short.io") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="shortio_status" value="1" id="shortio_status_1" {{ get_option("shortio_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="shortio_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="shortio_status" value="0" id="shortio_status_0"{{ get_option("shortio_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="shortio_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="shortio_api_key" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="shortio_api_key" id="shortio_api_key" type="text" value="{{ get_option("shortio_api_key", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="shortio_domain" class="form-label">{{ __('Domain') }}</label>
                            <input class="form-control" name="shortio_domain" id="shortio_domain" type="text" value="{{ get_option("shortio_domain", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Bitly") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="bitly_status" value="1" id="bitly_status_1" {{ get_option("bitly_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="bitly_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="bitly_status" value="0" id="bitly_status_0"{{ get_option("bitly_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="bitly_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="bitly_api_key" class="form-label">{{ __('API Token') }}</label>
                            <input class="form-control" name="bitly_api_key" id="bitly_api_key" type="text" value="{{ get_option("bitly_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("TinyURL") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tinyurl_status" value="1" id="tinyurl_status_1" {{ get_option("tinyurl_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="tinyurl_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tinyurl_status" value="0" id="tinyurl_status_0"{{ get_option("tinyurl_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="tinyurl_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="tinyurl_api_key" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="tinyurl_api_key" id="tinyurl_api_key" type="text" value="{{ get_option("tinyurl_api_key", "") }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">{{ __("Rebrandly") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="rebrandly_status" value="1" id="rebrandly_status_1" {{ get_option("rebrandly_status", 0)==1?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="rebrandly_status_1">
                                        {{ __('Enable') }}
                                    </label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="rebrandly_status" value="0" id="rebrandly_status_0"{{ get_option("rebrandly_status", 0)==0?"checked":"" }}>
                                    <label class="form-check-label mt-1" for="rebrandly_status_0">
                                        {{ __('Disable') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="rebrandly_api_key" class="form-label">{{ __('API Key') }}</label>
                            <input class="form-control" name="rebrandly_api_key" id="rebrandly_api_key" type="text" value="{{ get_option("rebrandly_api_key", "") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="rebrandly_domain" class="form-label">{{ __('Domain') }}</label>
                            <input class="form-control" name="rebrandly_domain" id="rebrandly_domain" type="text" value="{{ get_option("rebrandly_domain", "rebrand.ly") }}">
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