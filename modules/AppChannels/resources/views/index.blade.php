@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@php 
    $channels = Channels::channels();
@endphp

@section('sub_header')
    <x-sub-header 
        title="{{ __('Manage channels') }}" 
        description="{{ __('Seamless Management for All Channels') }}" 
        :count="$total"
    >

        <div class="d-flex gap-8">
            <a class="btn btn-dark btn-sm" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addChannelModal">
                <span><i class="fa-light fa-plus"></i></span>
                <span>{{ __('Add channels') }}</span>
            </a>
        </div>
    </x-sub-header>
@endsection

@section('content')
    <div class="container pb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="table-info"></div>
            <div class="d-flex flex-wrap gap-8">    
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
                        <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                            <i class="fa-light fa-filter"></i> {{ __("Filters") }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
                            <div class="d-flex border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                                <span><i class="fa-light fa-filter"></i></span>
                                <span>{{ __("Filters") }}</span>
                            </div>
                            <div class="p-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __("Status") }}</label>
                                    <select class="form-select ajax-scroll-filter" name="status">
                                        <option value="-1">{{ __("All") }}</option>
                                        <option value="1">{{ __("Active") }}</option>
                                        <option value="0">{{ __("Disconnected") }}</option>
                                        <option value="2">{{ __("Pause") }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">{{ __("Social network") }}</label>
                                    <select class="form-select ajax-scroll-filter" name="module_name">
                                        <option value="">{{ __("All") }}</option>
                                        @if( !empty( $channels ) )
                                            @foreach( $channels as $channel )

                                                @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                    @foreach( $channel['items'] as $item )
                                                        <option value="{{ $item['id'] }}">{{ $item['module_name'] }}</option>
                                                    @endforeach
                                                @endif

                                            @endforeach
                                        @endif
                                    </select>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="d-flex">
                    <div class="btn-group position-static">
                        <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                            <i class="fa-light fa-grid-2"></i> {{ __("Actions") }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125" data-popper-placement="bottom-end">
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/active") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-check"></i></span>
                                    <span>{{ __("Active") }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/pause") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pause"></i></span>
                                    <span>{{ __("Pause") }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true)">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __("Delete") }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ajax-scroll container px-4" data-url="{{ module_url("list") }}" data-resp=".channel-list" data-scroll="document">
        <div class="row channel-list">
        </div>
        <div class="pb-30 ajax-scroll-loading d-none">
            <div class="app-loading mx-auto mt-100 pl-0 pr-0">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <!-- Add Channels Modal -->
    <div class="modal modal-xl fade" id="addChannelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered1 modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header px-4">
                    <h1 class="modal-title fs-5">{{ __("Add channels") }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="row">
                        @if( !empty( $channels ) )
                            @foreach( $channels as $channel )
                                <div class="col-md-4 mb-4">
                                    <div class="card border-gray-300">
                                        <div class="card-body text-center d-flex flex-column justify-content-center align-items-center gap-10">
                                            <div class="d-flex align-items-center justify-content-center size-50 text-white border-1 b-r-100 fs-16" style="background-color: {{ $channel['color'] }};">
                                                <i class="{{ $channel['icon'] }}"></i>
                                            </div>
                                            <div class="fs-14 fw-5">{{ __($channel['name']) }}</div>
                                            <div>
                                                @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                    @foreach( $channel['items'] as $item )
                                                        <a href="{{ url($item["uri"]) }}" class="btn btn-outline btn-sm btn-light mb-1"><i class="fa-light fa-plus"></i> {{ __( ucfirst( str_replace("_", " ", $item["category"]) ) ) }}</a>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

