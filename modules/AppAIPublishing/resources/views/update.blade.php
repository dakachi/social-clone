@php
    $data = $result->data ?? [];
    $prompts = $result->prompts ?? [];
    $time_posts = $data['time_posts'] ?? [''];
@endphp

@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('AI Publishing Campaign Editor') }}" 
        description="{{ __('Effortlessly create or edit automated AI publishing campaigns.') }}" >
        <a class="btn btn-light btn-sm" href="{{ module_url("") }}">
            <span><i class="fa-light fa-angle-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
<form class="actionForm" action="{{ module_url("save") }}" method="POST" data-redirect="{{ module_url(); }}">
    <div class="container max-w-800 pb-5">

         <div class="mb-5">
            @if($result)
                <input class="form-control d-none" name="id" id="id" type="text" value="{{ old('name', $result->id_secure ?? '') }}">
            @endif

            <div class="border border-gray-200 b-r-6 p-3 mb-3">
                <label for="name" class="form-label">{{ __('Campaign name') }}</label>
                <input 
                    placeholder="{{ __('Campaign name') }}" 
                    class="form-control" 
                    name="name" 
                    id="name" 
                    type="text" 
                    value="{{ old('name', $result->name ?? '') }}">
            </div>

            <div class="border border-gray-200 b-r-6 p-3 mb-3">
               @include('appchannels::block_channels', ['accounts' => $result->accounts ?? []])
            </div>

            @foreach(app('channels')  as $value)

                @php
                    $view = $value['key'].'::options';
                @endphp

                @if(view()->exists($view))
                    <div class="d-none option-network" data-option-network="{{ $value['social_network'] }}">
                    @include($view)
                    </div>
                @endif
                

            @endforeach

            <div class="border border-gray-200 b-r-6 mb-3">
                @include('appaiprompts::block_prompts', ["prompts" => $prompts])
            </div>

            <div class="border border-gray-200 b-r-6 mb-3">
                <div class="d-flex border-bottom p-3 align-items-center justify-content-between">
                    <div class="fw-5 fs-14">{{ __("Options") }}</div>
                </div>

                <div class="row p-4">
                    @include("appaicontents::options", [
                        "include_media" => true,
                        "hashtags" => true,
                        "options" => $data
                    ])
                </div>
            </div>

            <div class="border border-gray-200 b-r-6 mb-3">
                <div class="d-flex border-bottom p-3 align-items-center justify-content-between">
                    <div class="fw-5 fs-14">{{ __("Schedule Regularly") }}</div>
                </div>

                <div class="">
                    <div class="px-4 pt-4 listPostByTimes">
                        @if($data['time_posts']??false)
                            @foreach(($data['time_posts'] ?? ['']) as $time)
                                <div class="input-group mb-3">
                                    <div class="form-control">
                                        <input class="onlytime" type="text" name="time_posts[]" value="{{ $time }}">
                                        <span class="btn btn-icon">
                                            <i class="fa-light fa-calendar-days text-gray-600"></i>
                                        </span>
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
                                    <span class="btn btn-icon">
                                        <i class="fa-light fa-calendar-days text-gray-600"></i>
                                    </span>
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
                        {{ __("End date") }}
                    </div>

                    <div class="p-3 overflow-x-auto">
                        <div class="form-control">
                            <input class="datetime" name="end_date" type="text" value="{{ old('end_date', (!empty($data['end_date']) ? datetime_show($data['end_date']) : '')) }}">
                            <span class="btn btn-icon">
                                <i class="fa-light fa-calendar-days text-gray-600"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">{{ __("Save changes") }}</button>

        </div>
    </div>
</form>
@endsection


@section('script')
    @section('script')
        <script type="text/javascript" src="{{ Module::asset(module("module_name").':resources/assets/js/ai_publishing.js'); }}"></script>
    @endsection
@endsection