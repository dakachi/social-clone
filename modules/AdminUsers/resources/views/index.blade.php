@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Users') }}" 
        description="{{ __('Easily Manage and Monitor All Platform Users') }}" 
        :count="$total_user"
    >
        <a class="btn btn-primary btn-sm" href="{{ url_admin("users/export") }}">
            <span><i class="fa-light fa-file-export"></i></span>
            <span>{{ __('Export') }}</span>
        </a>
        <a class="btn btn-dark btn-sm" href="{{ url_admin("users/create") }}">
            <span><i class="fa-light fa-user-plus"></i></span>
            <span>{{ __('Add user') }}</span>
        </a>
    </x-sub-header>
@endsection



@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-primary d-flex align-items-center justify-content-center bg-primary-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600">{{ __("Active") }}</div>
                            <div class="fw-7 fs-16">{{ Number::format($total_active_user) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-warning d-flex align-items-center justify-content-center bg-warning-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600">{{ __("Inactive") }}</div>
                            <div class="fw-7 fs-16">{{ Number::format($total_inactive_user) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-gray-200 mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-16">
                        <div class="size-45 fs-20 text-danger d-flex align-items-center justify-content-center bg-danger-100 b-r-10">
                            <i class="fa-light fa-user-check"></i>
                        </div>
                        <div class="text-end">
                            <div class="fs-12 text-gray-600">{{ __("Banned") }}</div>
                            <div class="fw-7 fs-16">{{ Number::format($total_banned_user) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
@component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script') 
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "" , "edit_url" => "" , "column_actions" => false, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
            {
                targets: 'fullname:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <a class="size-40 size-child border b-r-6 text-gray-800" href="{{ module_url("edit") }}/${row.id_secure}">
                                <img data-src="${ Main.mediaURL('{{ Media::url() }}', row.avatar) }" src="${ Main.text2img(row.fullname, '000') }" class="b-r-6 lazyload" onerror="this.src='${ Main.text2img(row.fullname, '000') }'">
                            </a>
                            <div class="text-start lh-1 text-truncate">
                                <div class="fw-5 text-gray-900 text-truncate">
                                    <div class="text-truncate">
                                        <a class="text-gray-800 text-hover-primary" href="{{ module_url("edit") }}/${row.id_secure}">
                                            ${row.fullname}
                                        </a>
                                    </div>
                                    <div class="text-truncate text-gray-500 fs-12">
                                        ${row.email}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }
            },
            {
                targets: 'role:name',
                orderable: true,
                className: 'min-w-80 text-danger text-center',
                render: function (data, type, row){
                
                    if (row.role == 2) {
                        return `<span class="text-warning fs-18" title="{{ __("Admin") }}"><i class="fa-duotone fa-solid fa-crown"></i></span>`;
                    }else{
                        return `<span class="text-gray-500 fs-18" title="{{ __("User") }}"><i class="fa-duotone fa-user"></i></span>`;
                    }
                    
                }
            },
            {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-end',
            render: function (data, type, row) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-light btn-active-light-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __("Actions") }}
                        </button>
                        <ul class="dropdown-menu border-1 border-gray-300 w-auto max-w-180 min-w-150">
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ module_url("edit") }}/${row.id_secure}">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                    <span>{{ $edit_text ?? __("Edit") }}</span>
                                </a>
                            </li>
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ url("auth/view-as-user") }}/${row.id_secure}">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                    <span>{{ $edit_text ?? __("View As User") }}</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @if(!isset($delete) || $delete)
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("destroy") }}" data-confirm="{{ __("Are you sure you want to delete this item?") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __("Delete") }}</span>
                                </a>
                            </li>
                            @endif        
                        </ul>
                    </div>
                `;
            },
        },          
        ]);
        var dtConfig = {
            columns: {!! json_encode($Datatable['columns'] ?? []) !!},
            lengthMenu: {!! json_encode($Datatable['lengthMenu'] ?? []) !!},
            order: {!! json_encode($Datatable['order'] ?? []) !!},
            columnDefs: {!! json_encode($Datatable['columnDefs'] ?? []) !!}
        };

        dtConfig.columnDefs = dtConfig.columnDefs.concat(columnDefs);
        var DataTable = Main.DataTable("#{{ $Datatable['element'] }}", dtConfig);
        DataTable.columns(['email:name', 'avatar:name']).visible(false);
    </script>
@endsection
