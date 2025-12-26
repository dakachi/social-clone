@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ $language->name }}" 
        description="{{ __('Update and manage words in language packages efficiently.') }}" 
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem" data-id="{{ $language->id_secure }}" href="{{ module_url("auto-translate/".$language->id_secure) }}" data-confirm="{{ __("Warning: This action will overwrite all your previous language changes. Are you sure you want to proceed with auto-translating this language?") }}" data-redirect="">
            <span><i class="fa-light fa-bolt-auto"></i></span>
            <span>{{ __('Auto Translate') }}</span>
        </a>
        <a class="btn btn-light btn-sm" href="{{ module_url() }}">
            <span><i class="fa-light fa-chevron-left"></i></span>
            <span>{{ __('Back') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable, "customTable" => true ])
        <table id="{{ $Datatable['element'] }}" data-url="{{ module_url("translations-list/".$language->id_secure) }}" class="display table table-bordered table-hide-footer w-100">
		    <thead>
		        <tr>
		        	@php
		        		$columns = $Datatable['columns'];
		        	@endphp
		            @foreach($columns as $key => $column)
		                @if($key + 1 == count($columns))
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
    @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "" , "column_actions" => false, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
                {
                    targets: 'name:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `<div class="text-gray-800 fs-13">${data}</div>`;
                    }
                },
                {
                    targets: 'value:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `<textarea class="form-control actionChange transaction_${row.id}" data-url="{{ module_url("update-translation") }}/${row.id}">${data}</textarea>`;
                    }
                },
                {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-end',
                render: function (data, type, row) {
                    return `
                        <button href="{{ module_url("auto-translation") }}/${row.id}" data-id="${row.id}" class="btn btn-light btn-icon btn-sm actionItem" data-call-success="Main.typeText('.transaction_${row.id}', result.text, 0, 1)">
                            <i class="fa-light fa-bolt-auto"></i>
                        </button>
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
        DataTable.columns(['id:name']).visible(false);
    </script>
@endsection