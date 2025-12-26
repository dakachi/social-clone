@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Bulk Post') }}" 
        description="{{ __('Manage and publish multiple posts efficiently and quickly') }}" 
    >
        <a class="btn btn-primary btn-sm" href="{{ module_url("download-template") }}">
            <span><i class="fa-light fa-table-layout"></i></span>
            <span>{{ __('Bulk Template') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    
    <div class="container pb-5 max-w-700">

        <form class="actionForm" action="{{ module_url("save") }}" method="POST" data-redirect="{{ module_url("") }}">
            
            <div class="card border-gray-300 mb-3 b-r-6">
                <div class="card-body py-5">
                    <div class="mb-3">
                        @include('appchannels::block_channels', [
                            'permission' => 'apppublishing', 
                            'accounts' => []
                        ])
                    </div>
                    <div class="mb-3">
                        @include('appfiles::block_select_file', [
                            "id" => "file",
                            "name" => __("Media CSV file"),
                            "required" => false,
                            "value" => ""
                        ])
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __("Interval per post (minute)") }}</label>
                        <input type="number" class="form-control mb-3" name="delay" value="60">

                        <div class="alert alert-warning fs-14" role="alert">
                            {{ __("If your posts are scheduled incorrectly or left empty, the system will automatically set the first post to the current time, with subsequent posts following a set interval delay.") }}
                        </div>
                    </div>
                    @if( get_option("url_shorteners_platform", 0) )
                    <div class="mb-0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="url_shorten" id="url_shorten">
                            <label class="form-check-label" for="url_shorten">
                                {{ __("Auto URL Shortener") }}
                            </label>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <button class="btn btn-dark w-100">{{ __("Save changes") }}</button>

        </form>
        
    </div>
@endsection