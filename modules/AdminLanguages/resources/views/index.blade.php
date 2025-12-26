@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Languages') }}" 
        description="{{ __('Manage all languages on your site') }}" 
        :count="$total"
    >
        <div>
            <label for="file-upload" class="btn btn-primary btn-sm">
                <span class="me-1 mt-1 text-center"><i class="fa-light fa-file-import"></i></span> {{ __("Import") }}
            </label>
            <input id="file-upload" data-url="{{ module_url("import") }}" class="d-none" name="file" type="file" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')" />
        </div>
        <a class="btn btn-dark btn-sm" href="{{ module_url("create") }}">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Add new') }}</span>
        </a>

    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "" , "column_actions" => false, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
                {
                    targets: 'name:name',
                    orderable: true,
                    render: function (data, type, row) {
                        var avatar = `<div class="d-flex justify-content-center align-items-center w-40 ratio ratio-4x3 border b-r-10 bg-gray-100 text-gray-400 fs-20"><i class="${row.icon}"></i></div>`; 

                        return `
                            <div class="d-flex gap-8 align-items-center">
                                <div>
                                    <a href="{{ module_url("edit") }}/${row.id_secure}">
                                        ${avatar}
                                    </a>
                                </div>
                                <div class="text-start lh-1.1">
                                    <div class="fw-5 text-gray-900">
                                        <a href="{{ module_url("edit") }}/${row.id_secure}" class="text-gray-800 text-hover-primary">
                                            ${row.name}
                                        </a>
                                    </div>
                                    <div class="text-gray-500 fs-12 text-uppercase">${row.code}</div>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 'is_default:name',
                    orderable: true,
                    className: 'min-w-80 text-danger text-center',
                    render: function (data, type, row){
                    
                        if (row.is_default == 1) {
                            return `<span class="text-success fs-18"><i class="fa-light fa-check"></i></span>`;
                        }else{
                            return `<span class="text-gray-500 fs-18"><i class="fa-light fa-xmark"></i></span>`;
                        }
                        
                    }
                },
                {
                    targets: 'auto_translate:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        if (row.auto_translate == 1) {
                            return `<span class="badge badge-outline badge-sm badge-primary">{{ __("Yes") }}</span>`;
                        }else{
                            return `<span class="badge badge-outline badge-sm badge-light">{{ __("No") }}</span>`;
                        }
                        
                    }
                },
                {
                    targets: 'dir:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        if (data == 'ltr') {
                            return `<span class="badge badge-outline badge-sm badge-danger text-uppercase">${data}</span>`;
                        }else{
                            return `<span class="badge badge-outline badge-sm badge-primary text-uppercase">${data}</span>`;
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
                            <ul class="dropdown-menu border-1 border-gray-300 w-auto max-w-300 min-w-200">
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ module_url("edit") }}/${row.id_secure}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                        <span>{{ __("Edit") }}</span>
                                    </a>
                                </li>
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ module_url("edit-translations") }}/${row.id_secure}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-language"></i></span>
                                        <span>{{ __("Edit Translations") }}</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ module_url("export") }}/${row.id_secure}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-file-export"></i></span>
                                        <span>{{ __("Export") }}</span>
                                    </a>
                                </li>
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("update-languages") }}/${row.id_secure}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-hands"></i></span>
                                        <span class="text-truncate">{{ __("Update Languages") }}</span>
                                    </a>
                                </li>
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("auto-translate") }}/${row.id_secure}" data-confirm="{{ __("Warning: This action will overwrite all your previous language changes. Are you sure you want to proceed with auto-translating this language?") }}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-earth-americas"></i></span>
                                        <span class="text-truncate">{{ __("Auto Translate") }}</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="mx-2">
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("destroy") }}" data-confirm="{{ __("Are you sure you want to delete this item?") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                        <span>{{ __("Delete") }}</span>
                                    </a>
                                </li>
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
        DataTable.columns(['code:name', 'icon:name']).visible(false);
    </script>
@endsection