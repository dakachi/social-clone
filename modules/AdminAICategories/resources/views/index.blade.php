@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@section('sub_header')
    <x-sub-header 
        title="{{ __('Manage AI Categories') }}" 
        description="{{ __('Oversee and organize various AI categories efficiently') }}" 
        :count="$total"
    >
        <div class="d-flex gap-8">
            <a class="btn btn-dark btn-sm actionItem" href="{{ module_url("update") }}" data-popup="AICategoriesModal" data-bs-target="#staticBackdrop">
                <span><i class="fa-light fa-plus"></i></span>
                <span>{{ __('Create new') }}</span>
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
                                        <option value="1">{{ __("Enable") }}</option>
                                        <option value="0">{{ __("Disable") }}</option>
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
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/enable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                    <span >{{ __('Enable') }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/disable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-eye-slash"></i></span>
                                    <span>{{ __('Disable') }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>                    
                            <li>
                                <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true);">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __("Delete") }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 border-0">
            @if(!empty($Datatable['columns']))
            <div class="table-responsive">
                <table id="{{ $Datatable['element'] }}" data-url="{{ module_url("list") }}" class="display table table-hide-footer w-100">
                    <thead>
                        <tr>
                            @foreach($Datatable['columns'] as $key => $column)

                                @if($key == 0)

                                @elseif($key + 1 == count($Datatable['columns']))
                                    <th class="align-middle w-120 max-w-100">
                                        {{ __('Actions') }}
                                    </th>
                                @else
                                    <th class="align-middle">
                                        {{ $column['data'] }}
                                    </th>
                                @endif

                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="fs-14">
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
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
