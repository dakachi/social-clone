@extends('layouts.app')

@section('form', json_encode([
    'action' => module_url("save"),
    'method' => 'POST',
    'class' => 'actionForm',
    'data-redirect' => module_url()
]))

@section('sub_header')
    <x-sub-header 
        title="{{ $result ? __('Edit RSS Schedule') : __('Create New Rss Schedule') }}" 
        description="{{ $result ? __('Automate posting content from RSS feeds efficiently.') : __('Set up automated posts from your RSS feed.') }}" 
    >
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')

    <div class="container pb-5 max-w-800">

        <input type="text" class="d-none" name="id_secure" value="{{ old('id_secure', $result->id_secure ?? '') }}">

        <div class="card border-gray-300 mb-3 b-r-6">
            <div class="card-body">
                <div class="mb-0">
                    @include('appchannels::block_channels', [
                        'permission' => 'apppublishing', 
                        'accounts' => $accounts ?? []
                    ])
                </div>
            </div>
        </div>

        <div class="card border-gray-300 mb-3 b-r-6">
            <div class="card-body">
                <div class="mb-0">
                    <label class="form-label">{{ __("Rss Url") }}</label>
                    <input type="text" class="form-control mb-3" name="url"
                        value="{{ old('url', $result->url ?? '') }}"
                        placeholder="{{ __("Enter your RSS Url") }}">
                </div>
            </div>
        </div>

        <div class="card border-gray-300 mb-3 b-r-6">
            <div class="card-header">
                <div class="fs-14 fw-5">{{ __("Settings") }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    @if( get_option("url_shorteners_platform", 0) )
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="url_shorten" id="url_shorten" {{ old('url_shorten', $data['url_shorten'] ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="url_shorten">
                                    {{ __("Auto URL Shortener") }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="deny_link" id="deny_link" {{ old('deny_link', $data['deny_link'] ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="deny_link">
                                    {{ __("Publish posts without link") }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="accept_caption" id="accept_caption" {{ old('accept_caption', $data['accept_caption'] ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="accept_caption">
                                    {{ __("Post the caption") }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-0">{{ __("Refferal Code") }}</label>
                    <div class="mb-1 fs-12 text-gray-500">{{ __('A unique code used to refer new users and track invitations.') }}</div>
                    <input type="text" class="form-control mb-3" name="referral_code" value="{{ old('referral_code', $data['referral_code'] ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __("Only publish posts containing these words") }}</label>
                     <textarea class="form-control" name="include_keywords">{{ old('include_keywords', $data['include_keywords'] ?? '') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __("Do not publish posts containing these words") }}</label>
                    <textarea class="form-control" name="ignore_keywords">{{ old('ignore_keywords', $data['ignore_keywords'] ?? '') }}</textarea>
                </div>
                
            </div>
        </div>

        <div class="border border-gray-200 b-r-6 mb-3">
            <div class="d-flex border-bottom p-3 align-items-center justify-content-between">
                <div class="fw-5 fs-14">{{ __("Schedule Regularly") }}</div>
            </div>

            <div class="mb-0">
                <div class="selectTimes">
                    <div class="px-4 pt-4 listPostByTimes">
                        @if($data['time_posts']??false)
                            @foreach(($data['time_posts'] ?? ['']) as $time)
                                <div class="input-group mb-3">
                                    <div class="form-control">
                                        <input class="onlytime" type="text" name="time_posts[]" value="{{ $time }}">
                                        <button class="btn btn-icon">
                                            <i class="fa-light fa-calendar-days text-gray-600"></i>
                                        </button>
                                    </div>
                                    <span class="btn btn-input remove">
                                        <i class="fa-light fa-trash-can text-gray-900"></i>
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="input-group mb-3">
                                <div class="form-control">
                                    <input class="onlytime" type="text" name="time_posts[]" value="">
                                    <button class="btn btn-icon">
                                        <i class="fa-light fa-calendar-days text-gray-600"></i>
                                    </button>
                                </div>
                                <span class="btn btn-input remove">
                                    <i class="fa-light fa-trash-can text-gray-900"></i>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="px-4 pb-4">
                        <button type="button" class="btn btn-outline btn-dark w-100 addSpecificTimes">
                            <i class="fa-light fa-plus"></i> {{ __("Add time") }}
                        </button>
                    </div>
                    
                    <div class="tempPostByTimes d-none">
                        <div class="input-group mb-3">
                            <div class="form-control">
                                <input class="" type="text" value="">
                                <button class="btn btn-icon">
                                    <i class="fa-light fa-calendar-days text-gray-600"></i>
                                </button>
                            </div>
                            <span class="btn btn-input remove">
                                <i class="fa-light fa-trash-can text-gray-900"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-bottom border-top px-4 py-2 fs-12 bg-gray-100 text-gray-600">
                    {{ __("Schedule every") }}
                </div>

                @php
                    $weekdays = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
                @endphp

                <div class="p-3 overflow-x-auto">
                    <div class="d-flex gap-8 min-w-400">
                        @foreach($weekdays as $value)
                        <label for="{{ $value }}" class="flex-fill w-100 ratio-1x1 b-r-6 border border-gray-200 py-4 text-center bg-active-primary fw-3">
                            {{ __($value) }}  
                            <input class="form-check-input d-none" type="checkbox"  name="weekdays[{{ $value }}]" value="1" id="{{ $value }}" data-add-class="bg-primary text-white" {{ (isset($data['weekdays'][$value]) && $data['weekdays'][$value]) ? 'checked' : '' }}>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-bottom border-top px-4 py-2 fs-12 bg-gray-100 text-gray-600">
                    {{ __("Start date") }}
                </div>

                <div class="p-3 overflow-x-auto">
                    <div class="form-control">
                        <input class="datetime" name="start_date" type="text" value="{{ old('start_date', (!empty($data['start_date']) ? datetime_show($data['start_date']) : '')) }}">
                        <button class="btn btn-icon">
                            <i class="fa-light fa-calendar-days text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <div class="border-bottom border-top px-4 py-2 fs-12 bg-gray-100 text-gray-600">
                    {{ __("End date") }}
                </div>

                <div class="p-3 overflow-x-auto">
                    <div class="form-control">
                        <input class="datetime" name="end_date" type="text" value="{{ old('end_date', (!empty($data['end_date']) ? datetime_show($data['end_date']) : '')) }}">
                        <button class="btn btn-icon">
                            <i class="fa-light fa-calendar-days text-gray-600"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-dark w-100">{{ __("Save changes") }}</button>
        
    </div>
@endsection