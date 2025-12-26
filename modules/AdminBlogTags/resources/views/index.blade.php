@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Blog Tags') }}"
        description="{{ __('Easily create, edit, and manage blog tags seamlessly!') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem" href="{{ module_url("update") }}" data-popup="BlogTagsModal" data-bs-target="#staticBackdrop">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Add new') }}</span>
        </a>
    </x-sub-header>
@endsection


@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "BlogTagsModal" , "edit_url" => "BlogTagsModal" , "column_actions" => true, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
            {
                targets: 'name:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <div class="text-start lh-1.1">
                                <div class="fw-5 text-${row.color}">
                                    <a href="{{ module_url("update") }}" data-id="${row.id_secure}" class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-popup="BlogTagsModal"
                                        class="text-gray-800 text-hover-primary">
                                        ${row.name}
                                    </a>
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
        DataTable.columns(['color:name']).visible(false);
    </script>
@endsection
