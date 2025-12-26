@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@section('sub_header')
    <x-sub-header 
        title="{{ __('Manage groups') }}" 
        description="{{ __('Effortlessly Organize and Manage All Your Groups') }}" 
        :count="$total"
    >
        <div class="d-flex">
            <div class="form-control form-control-sm">
                <span class="btn btn-icon">
                    <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                </span>
                <input class="ajax-scroll-filter" name="keyword" placeholder="{{ __('Search') }}" type="text">
                <button class="btn btn-icon">
                    <div class="form-check form-check-sm mb-0">
                        <input class="form-check-input checkbox-all" id="select_all" type="checkbox">
                    </div>
                </button>
            </div>
        </div>
        <div class="d-flex">
            <div class="btn-group position-static">
                <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fa-light fa-grid-2"></i> {{ __("Actions") }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true);">
                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                            <span>{{ __("Delete") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="d-flex gap-8">
            <a class="btn btn-dark btn-sm actionItem" href="{{ module_url("update") }}" data-popup="groupModal" data-call-success="Main.ajaxScroll(true);">
                <span><i class="fa-light fa-plus"></i></span>
                <span>{{ __('Create new') }}</span>
            </a>
        </div>
    </x-sub-header>
@endsection


@section('content')
    <div class="container px-4">

        <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".channel-list" data-scroll="document">

            <div class="row channel-list">
                
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