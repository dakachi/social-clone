@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Proxies') }}" 
        description="{{ __('Seamlessly Power Your Features with Reliable Proxies') }}" 
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
        columnDefs  = columnDefs.concat([]);
        
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
