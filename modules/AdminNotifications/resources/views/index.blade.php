@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Manual Notifications') }}"
        description="{{ __('Send custom messages manually to selected user accounts.') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem" href="{{ module_url('update') }}"  data-popup="sendNotificationModal">
            <span><i class="fa-light fa-plus"></i></span>
            <span>{{ __('New Notification') }}</span>
        </a>
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "sendNotificationModal" , "column_actions" => true, "column_status" => true]) @endcomponent

    <script type="text/javascript">
        columnDefs  = columnDefs.concat([
            {
                targets: 'title:name',
                orderable: true,
                render: function (data, type, row) {
                    var url = '';
                    if(row.url){
                        var url = `<div class="fs-12">
                            <a href="${row.url}" class="text-primary">${row.url}</a>
                        </div>`;
                    }

                    return `
                        <div class="fs-14 d-flex flex-column gap-6">
                            <div class="fw-6">${row.title}</div>
                            <div>${ Main.nl2br(row.message) }</div>
                            ${url}
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
        DataTable.columns(['message:name', 'url:name', 'created_by:name']).visible(false);
    </script>
@endsection
