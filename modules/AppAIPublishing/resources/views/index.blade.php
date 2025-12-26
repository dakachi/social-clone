@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('AI Publishing') }}"
        description="{{ __('Automating content creation and publishing with ease') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm" href="{{ module_url("create") }}">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Create new') }}</span>
        </a>
    </x-sub-header>
@endsection


@section('content')

    <div class="container pb-5">

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".ai-posts-data" data-scroll="document">

            <div class="row ai-posts-data">
                <div class="mb-50"></div>
            </div>

            <div class="pb-30 ajax-scroll-loading d-none">
                <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    @section('script')
        <script type="text/javascript" src="{{ Module::asset(module("module_name").':resources/assets/js/ai_publishing.js'); }}"></script>
    @endsection
@endsection
