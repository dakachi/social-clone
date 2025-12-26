@extends('layouts.app')


@section('sub_header')
    <x-sub-header
        title="{{ __('Frequently Asked Questions') }}"
        description="{{ __('Essential answers to common queries for immediate clarity') }}"
        :count="$total"
    >
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
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "" , "edit_url" => "" , "column_actions" => true, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
             {
                targets: 'title:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <div class="text-start lh-1 text-truncate">
                                <div class="fw-5 text-gray-900 text-truncate">
                                    <div class="text-truncate">
                                        <a class="text-gray-800 text-hover-primary" href="{{ module_url("edit") }}/${row.id_secure}">
                                            ${row.title}
                                        </a>
                                    </div>
                                    <div class="text-truncate text-gray-500 fs-12">
                                        ${row.content}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }
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

        DataTable.columns(['thumbnail:name', 'content:name','desc:name']).visible(false);
    </script>
@endsection
