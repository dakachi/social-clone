@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Coupons') }}" 
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
    @component('components.datatable_script', [ "Datatable" => $Datatable ]) @endcomponent
    <script type="text/javascript">

        columnDefs = columnDefs.concat([
            {
                targets: 'usage_limit:name',
                orderable: true,
                render: function (data, type, row) {
                    switch(data){
                        case -1:
                            return `<span class="text-info fs-12">{{ __("Unlimited") }}</span>`;
                            break;

                        default:
                            return `<span class="">${data}</span>`;
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

        // Initialize the DataTable
        var DataTable = Main.DataTable("#{{ $Datatable['element'] }}", dtConfig);
        DataTable.columns([]).visible(false);
    </script>
@endsection