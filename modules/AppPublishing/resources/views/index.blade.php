@extends('layouts.app')
@include('apppublishing::header_center', [])

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ Module::asset('AppPublishing:resources/assets/css/publishing.css'); }}">
@endsection

@section('header_end')
    <div class="compose_header position-absolute w-100 t-0 l-0 d-flex justify-content-between align-items-center zIndex-9 bg-white h-70 border-bottom px-4 d-none">
        <div class="fw-6 fs-18">{{ __("New Post") }}</div>
        <div class="fw-6 fs-18">
            <div class="btn btn-icon btn-light btn-hover-danger b-r-50 a-rotate closeCompose">
                <i class="fa-light fa-xmark"></i>
            </div>
        </div>
    </div>
    <a class="btn btn-dark btn-sm actionItem b-r-50 text-nowrap" href="{{ module_url("composer") }}" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();"><i class="fa-light fa-calendar-lines-pen"></i> {{ __("Compose") }}</a>
@endsection


@section('content')

    <div class="composer-scheduling position-absolute zIndex-9 wp-100 hp-100 d-none"></div>

    <div class="compose-calendar overflow-hidden position-relative">
        <form method="POST">
            
            <div class="calendar-header d-flex flex-wrap gap-8 justify-content-between align-items-center px-4 py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center gap-8 w-sm-100">
                    <div>
                        <div class="btn btn-sm btn-light rounded-circle border-gray-300 size-32 fs-20 calendar-event" data-calendar-type="prev">
                            <i class="fa-light fa-angle-left"></i>
                        </div>
                    </div>
                    <div class="fs-16 fw-6 text-gray-800 calendar-title d-block d-md-none"></div>
                    <div>
                        <div class="btn btn-sm btn-light rounded-circle border-gray-300 size-32 fs-20 calendar-event" data-calendar-type="next">
                            <i class="fa-light fa-angle-right"></i>
                        </div>
                    </div>
                    <div class="d-none d-md-block">
                        <div class="btn btn-sm btn-light b-r-50 border-gray-300 calendar-event" data-calendar-type="today">{{ __("Today") }}</div>
                    </div>
                </div>
                <div class="fs-20 fw-6 text-gray-800 calendar-title d-none d-md-block"></div>
                <div class="d-flex flex-wrap gap-8 justify-content-center align-items-center w-sm-100">
                    <div class="btn-group btn-group-sm d-none d-sm-block">
                        <button type="button" class="btn btn-light calendar-event" data-calendar-type="dayGridMonth" data-bs-title="{{ __("Month view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="fa-light fa-calendar-days"></i>
                        </button>
                        <button type="button" class="btn btn-light calendar-event" data-calendar-type="timeGridWeek" data-bs-title="{{ __("Week view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="fa-light fa-columns-3"></i>
                        </button>
                        <button type="button" class="btn btn-light calendar-event" data-calendar-type="listWeek" data-bs-title="{{ __("List view") }}" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="fa-duotone fa-light fa-list-ul"></i>
                        </button>
                    </div>

                    <div class="d-flex">
                        <div class="btn-group position-static">
                            <button type="button" class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
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
                                        <select class="form-select calendar-filter" name="status">
                                            <option value="">{{ __("All") }}</option>
                                            <option value="3">{{ __("Processing") }}</option>
                                            <option value="4">{{ __("Published") }}</option>
                                            <option value="5">{{ __("Unpublished") }}</option>
                                            <option value="1">{{ __("Active") }}</option>
                                            <option value="2">{{ __("Waiting Approve") }}</option>
                                            <option value="6">{{ __("Pause/Stop") }}</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{ __("Social network") }}</label>
                                        <select class="form-select calendar-filter" name="module_name">
                                            <option value="">{{ __("All") }}</option>
                                            @if( !empty( Channels::channels() ) )
                                                @foreach( Channels::channels() as $channel )
                                                    
                                                    @if( !empty( $channel ) && isset( $channel['items']  ) )
                                                        @foreach( $channel['items'] as $item )
                                                            <option value="{{ $item['id'] }}">{{ $item['module_name'] }}</option>
                                                        @endforeach
                                                    @endif
                                           
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">{{ __("Campaign") }}</label>
                                        <select class="form-select calendar-filter" name="campaign">
                                            <option value="">{{ __("All") }}</option>
                                            @if( !empty( $campaigns ) )
                                                @foreach( $campaigns as $value )
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">{{ __("Labels") }}</label>
                                        <select class="form-select calendar-filter" name="label">
                                            <option value="">{{ __("All") }}</option>
                                            @if( !empty( $labels ) )
                                                @foreach( $labels as $value )
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="btn-group">
                            <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
                                <i class="fa-light fa-grid-2"></i> {{ __('Actions') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-150">
                                <li>
                                    <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy-by-filters") }}" data-confirm="{{ __("Delete all scheduled posts matching your filters. Are you sure?") }}" data-call-success="AppPubishing.reloadCalendar();">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                        <span>{{ __('Delete') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="calendar-scroll">
                <div id='calendar'></div>
            </div>

            <div class="calendar-add-button d-none">
                <a class="add-button wp-100 bg-dark text-white l-0 b-0 px-3 py-2 fs-14 fw-6 text-center actionItem" href="{{ module_url("composer") }}?date=[[date]]" data-append-content="composer-scheduling" data-call-success="AppPubishing.openCompose();">
                    <i class="fa-light fa-plus"></i> {{ __("Add post") }}
                </a>
            </div>

            <div class="calendar-event-item d-none">
                <div class="card text-wrap border-2 mb-1 shadow-none [[border_color]] event-item wp-100" data-id="[[id]]" data-url="{{ module_url("changePostDate") }}" data-loading="0">
                    <div class="card-body px-2 py-2">
                        <div class="d-flex flex-grow-1 align-items-top gap-8 w-100 mb-2 fs-11">
                            <div class="flex-grow-1 text-truncate">
                                <div class="d-flex fw-5 gap-6 align-items-center">
                                    <i class="[[icon]] fs-12" style="color: [[color]]"></i>
                                    <span class="text-truncate">[[account_name]]</span>
                                </div>
                                
                            </div>
                            <div>
                                <div class="text-gray-900 fw-5">[[time_post]]</div>
                                
                            </div>
                        </div>
                        <div class="d-flex flex-grow-1 align-items-top gap-8 w-100 mb-2">
                            <div class="flex-grow-1 fs-10 text-truncate-2">
                                <div class="text-gray-600 text-truncate-2">[[caption]]</div>
                            </div>
                            <div class="size-40 min-w-40 b-r-6 border-1 fs-18 text-primary bg-gray-100 d-flex align-items-center justify-content-center bg-cover video">
                                [[media]]
                            </div>
                        </div>
                        <div class="d-flex justify-content-between fs-11 gap-8">
                            [[status]]
                            <div class="d-flex gap-8">
                                <a href="{{ url_app("publishing/preview") }}" class="text-gray-900 actionItem" data-popup="pubishingPreviewModal" data-id="[[id]]" data-call-success="AppPubishing.closePopoverCalendar();"><i class="fa-light fa-eye"></i></a>
                                <a href="{{ url_app("publishing/composer") }}" class="text-gray-900 actionItem" data-append-content="composer-scheduling" data-id="[[id]]" data-call-success="AppPubishing.openCompose(); AppPubishing.closePopoverCalendar();"><i class="fa-light fa-pen-to-square"></i></a>
                                <div class="btn-group position-static">
                                    <div class="dropdown-toggle dropdown-arrow-hide text-gray-900 fs-11" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa-light fa-grid-2"></i>
                                    </div>
                                    <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                                        <li>
                                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionItem" href="{{ module_url("destroy") }}" data-id="[[id]]" data-call-success="AppPubishing.reloadCalendar();">
                                                <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                                <span>{{ __('Delete') }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="calendar-status d-none" data-status="1">
                <div class="text-dark text-truncate">
                    <i class="fa-light fa-pen-to-square"></i>
                    <span>{{ __("Draft") }}</span>
                </div>
            </div>

            <div class="calendar-status d-none" data-status="3">
                <div class="text-primary text-truncate">
                    <i class="fa-light fa-loader fa-spin"></i>
                    <span>{{ __("Processing") }}</span>
                </div>
            </div>

            <div class="calendar-status d-none" data-status="2">
                <div class="text-warning text-truncate">
                    <i class="fa-solid fa-people-group"></i>
                    <span>{{ __("Waiting Approve") }}</span>
                </div>
            </div>

            <div class="calendar-status d-none" data-status="4">
                <div class="d-flex gap-8">
                    <div class="text-success text-truncate">
                        <i class="fa-light fa-circle-check"></i>
                        <span>{{ __("Published") }}</span>
                    </div>
                    <a href="[[posted_link]]" target="_blank" class="text-success"><i class="fa-light fa-arrow-up-right-from-square"></i></a>
                </div>
            </div>

            <div class="calendar-status d-none" data-status="5">
                <div class="d-flex gap-8">
                    <div class="text-danger text-truncate">
                        <i class="fa-light fa-circle-check"></i>
                        <span>{{ __("Unpublished") }}</span>
                    </div>
                    <span class="text-danger" data-bs-title="[[msg]]" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fa-light fa-circle-question"></i></span>
                </div>
            </div>

            <div class="calendar-media-view d-none" data-type="1">
                <i class="fa-light fa-align-center"></i>
            </div>

            <div class="calendar-media-view d-none" data-type="2">
                <a href="[[link]]" target="_blank" >
                    <i class="fa-light fa-link"></i>
                </a>
            </div>

            <div class="calendar-media-view d-none" data-type="3">
            </div>

            <div class="calendar-media-view d-none" data-type="4">
                <i class="fa-solid fa-circle-play"></i>
            </div>

        </form>

    </div>
@endsection