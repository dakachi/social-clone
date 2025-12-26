@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('RSS Schedules') }}" 
        description="{{ __('Schedule and automate RSS feed content posting') }}" 
    >
        <a class="btn btn-dark btn-sm" href="{{ module_url("create") }}">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Add new') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    <div class="container">

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".rss-list" data-scroll="document">

            <div class="row rss-list">
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