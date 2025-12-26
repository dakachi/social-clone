@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Appearance') }}"
        description="{{ __('The interface matches brand and preferences') }}"
    >
    </x-sub-header>
@endsection

@section('content')

<div class="container max-w-800 pb-5">
    <form class="actionForm" action="{{ url_admin('settings/save') }}" method="POST">
        @csrf
        <div class="card shadow-none border-gray-300 mb-4">
            <div class="card-header">
                <div class="fw-6">
                    {{ __("Backend configure") }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Sidebar type --}}
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Sidebar type') }}</label>
                            <div class="mb-0">
                                <div class="d-flex gap-4 flex-column flex-lg-row">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_sidebar_type" value="1" id="backend_sidebar_type_2" {{ get_option("backend_sidebar_type", 1)==1?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_sidebar_type_2">
                                            {{ __('Hover') }}
                                        </label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_sidebar_type" value="0" id="backend_sidebar_type_0" {{ get_option("backend_sidebar_type", 1)==0?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_sidebar_type_0">
                                            {{ __('Open') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Theme color --}}
                    <div class="col-md-12 d-none">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Theme color') }}</label>
                            <div class="mb-0">
                                <div class="d-flex gap-4 flex-column flex-lg-row">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_theme_color" value="1" id="backend_theme_color_1" {{ get_option("backend_theme_color", 1)==1?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_theme_color_1">
                                            {{ __('Dark') }}
                                        </label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_theme_color" value="0" id="backend_theme_color_0" {{ get_option("backend_theme_color", 1)==0?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_theme_color_0">
                                            {{ __('Light') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Sidebar icon color --}}
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label">{{ __('Sidebar icon color') }}</label>
                            <div class="mb-0">
                                <div class="d-flex flex-column flex-lg-row">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_sidebar_icon_color" value="1" id="backend_sidebar_icon_color_1" {{ get_option("backend_sidebar_icon_color", 1)==1?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_sidebar_icon_color_1">
                                            {{ __('Default') }}
                                        </label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="backend_sidebar_icon_color" value="0" id="backend_sidebar_icon_color_0" {{ get_option("backend_sidebar_icon_color", 1)==0?"checked":"" }}>
                                        <label class="form-check-label mt-1" for="backend_sidebar_icon_color_0">
                                            {{ __('Custom Color') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Custom color --}}
                    <div class="col-md-12">
                        <div class="mb-4">
                            <label class="form-label" for="backend_site_icon_color">{{ __('Custom color') }}</label>
                            <input class="form-control w-80" name="backend_site_icon_color" id="backend_site_icon_color" type="color" value="{{ get_option('backend_site_icon_color', '') }}">
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

