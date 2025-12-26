@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Plan Management') }}"
        description="{{ __('Seamlessly manage subscription plans to drive revenue growth.') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm" href="{{ module_url('create') }}">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Create new') }}</span>
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
                targets: 'type:name',
                orderable: true,
                render: function (data, type, row) {
                    switch(data){
                        case 1:
                            return `<span class="badge badge-xs badge-pill badge-outline badge-info">{{ __("Monthly") }}</span>`;
                            break;

                        case 2:
                            return `<span class="badge badge-xs badge-pill badge-outline badge-success">{{ __("Yearly") }}</span>`;
                            break;

                        case 3:
                            return `<span class="badge badge-xs badge-pill badge-outline badge-primary">{{ __("Lifetime") }}</span>`;
                            break;
                    }
                }
            },
            {
                targets: 'featured:name',
                orderable: true,
                render: function (data, type, row) {
                    switch(data){
                        case 0:
                            return `<span class="fs-18 text-danger"><i class="fa-light fa-circle-xmark"></i></span>`;
                            break;

                        case 1:
                            return `<span class="fs-18 text-success"><i class="fa-light fa-circle-check"></i></span>`;
                            break;
                    }
                }
            },
            {
                targets: 'free_plan:name',
                orderable: true,
                render: function (data, type, row) {
                    switch(data){
                        case 0:
                            return `<span class="fs-18 text-danger"><i class="fa-light fa-circle-xmark"></i></span>`;
                            break;

                        case 1:
                            return `<span class="fs-18 text-success"><i class="fa-light fa-circle-check"></i></span>`;
                            break;
                    }
                }
            }
        ]);
        var dtConfig = {
            columns: {!! json_encode($Datatable['columns'] ?? []) !!},
            lengthMenu: {!! json_encode($Datatable['lengthMenu'] ?? []) !!},
            order: {!! json_encode($Datatable['order'] ?? []) !!},
            columnDefs: {!! json_encode($Datatable['columnDefs'] ?? []) !!}
        };

        dtConfig.columnDefs = dtConfig.columnDefs.concat(columnDefs);
        var DataTable = Main.DataTable("#{{ $Datatable['element'] }}", dtConfig);
        DataTable.columns([]).visible(false);
    </script>
@endsection