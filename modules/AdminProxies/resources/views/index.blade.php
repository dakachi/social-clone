@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('System Proxies') }}" 
        description="{{ __('Manage and maintain high-quality proxy resources for all platform features.') }}" 
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem" href="{{ module_url('update') }}" data-popup="proxiesModal" >
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('Create new') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "proxiesModal" , "column_actions" => true, "column_status" => true]) @endcomponent

    <script type="text/javascript">
        columnDefs = columnDefs.concat([
            {
                targets: 'is_free:name',
                orderable: true,
                className: 'min-w-80 text-danger text-center',
                render: function (data, type, row){
                
                    if (row.is_free == 1) {
                        return `<span class="badge badge-outline badge-sm badge-primary">{{ __("Yes") }}</span>`;
                    }else{
                        return `<span class="tbadge badge-outline badge-sm badge-danger">{{ __("No") }}</span>`;
                    }
                    
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
        DataTable.columns([]).visible(false);
    </script>
@endsection
