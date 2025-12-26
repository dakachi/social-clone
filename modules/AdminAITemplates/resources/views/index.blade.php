@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('AI Templates') }}"
        description="{{ __('Pre-made formats designed for use with AI.') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem" href="{{ module_url('update') }}"  data-popup="AITemplatesModal">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Create new') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "AITemplatesModal" , "column_actions" => true, "column_status" => true]) @endcomponent

    <script type="text/javascript">
        columnDefs  = columnDefs.concat([
            {
                targets: 'ai_categories.name:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="fs-14 d-flex gap-6 align-items-center">
                            <i class="${row.ai_categories_icon} text-${row.ai_categories_color} fs-14" ></i> ${row.ai_categories_name}
                        </div>
                        `;
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
        DataTable.columns(['ai_categories.icon:name', 'ai_categories.color:name']).visible(false);
    </script>
@endsection
