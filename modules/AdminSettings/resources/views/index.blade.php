@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('General Settings') }}"
        description="{{ __('Set up core application preferences') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin("settings/save") }}">
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Website Settings") }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Website Title') }}</label>
                            <input class="form-control" name="website_title" id="website_title" type="text" value="{{ get_option("website_title", config('site.title')) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Website Description') }}</label>
                            <input class="form-control" name="website_description" id="website_description" type="text" value="{{ get_option("website_description", config('site.description')) }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Website Keyword') }}</label>
                            <input class="form-control" name="website_keyword" id="website_keyword" type="text" value="{{ get_option("website_keyword", config('site.keywords')) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- logo settings --}}
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Logo Settings") }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-12">
                        <div class="mb-4">
                            @include('appfiles::block_select_file', [
                            "id" => "website_favicon",
                            "name" => __("Website favicon"),
                            "required" => false,
                            "value" => get_option("website_favicon", asset('public/img/favicon.png'))
                        ])
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            @include('appfiles::block_select_file', [
                            "id" => "website_logo_dark",
                            "name" => __("Website logo dark"),
                            "required" => false,
                            "value" => get_option("website_logo_dark", asset('public/img/logo-dark.png'))
                        ])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            @include('appfiles::block_select_file', [
                            "id" => "website_logo_light",
                            "name" => __("Website logo light"),
                            "required" => false,
                           "value" => get_option("website_logo_light", asset('public/img/logo-light.png'))
                        ])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                             @include('appfiles::block_select_file', [
                            "id" => "website_logo_brand_dark",
                            "name" => __("Website logo brand dark"),
                            "required" => false,
                            "value" => get_option("website_logo_brand_dark", asset('public/img/logo-brand-dark.png'))
                        ])
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            @include('appfiles::block_select_file', [
                            "id" => "website_logo_brand_light",
                            "name" => __("Website logo brand light"),
                            "required" => false,
                            "value" => get_option("website_logo_brand_light", asset('public/img/logo-brand-light.png'))
                        ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Date and Time Formats") }}
                </div>
            </div>
            <div class="card-body">
    <div class="row">
        <div class="col-md-6 mb-4">
            <label for="format_date" class="form-label">
                {{ __('Date') }}
            </label>
            <select class="form-select" name="format_date" id="format_date">
                @foreach(getDateFormats() as $key => $example)
                    <option value="{{ $key }}"
                        @selected(get_option('format_date', getDefaultDateFormat()) == $key)>
                        {{ $example }} ({{ $key }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-4">
            <label for="format_datetime" class="form-label">
                {{ __('Date and Time') }}
            </label>
            @php
                $selectedFormat = (string) get_option('format_datetime', getDefaultDateTimeFormat());
            @endphp
            <select class="form-select" name="format_datetime" id="format_datetime">
                @foreach(getDateTimeFormats() as $key => $example)
                    <option value="{{ $key }}"
                        {{ $selectedFormat === (string) $key ? 'selected' : '' }}>
                        {{ $example }} ({{ $key }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
        </div>
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Contact Settings") }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Company Name') }}</label>
                            <input class="form-control" name="contact_company_name" id="contact_company_name" type="text" value="{{ get_option("contact_company_name", "Your Company Name") }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Company Website') }}</label>
                            <input class="form-control" name="contact_company_website" id="contact_company_website" type="text" value="{{ get_option("contact_company_website", "https://yourcompany.com") }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Email Address') }}</label>
                            <input class="form-control" name="contact_email" id="contact_email" type="text" value="{{ get_option("contact_email", "support@yourcompany.com") }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Phone Number') }}</label>
                            <input class="form-control" name="contact_phone_number" id="contact_phone_number" type="text" value="{{ get_option("contact_phone_number", "+1 234 567 890") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Working Hours') }}</label>
                            <input class="form-control" name="contact_working_hours" id="contact_working_hours" type="text" value="{{ get_option("contact_working_hours", "Mon - Fri: 09:00 AM - 06:00 PM") }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label for="name" class="form-label">{{ __('Location') }}</label>
                            <input class="form-control" name="contact_location" id="contact_location" type="text" value="{{ get_option("contact_location", "123 Main Street, City, Country") }}">
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
        </div>



    </form>
</div>

@endsection
