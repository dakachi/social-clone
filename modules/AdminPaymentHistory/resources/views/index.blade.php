@extends('layouts.app')

@section('sub_header')
    <x-sub-header
        title="{{ __('Payment History') }}"
        description="{{ __('Show payment amounts dates statuses and methods') }}"
        :count="$total"
    >
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable , "column_status" => true, "column_actions" => false]) @endcomponent

    <script type="text/javascript">
        Main.DateTime();
        columnDefs  = columnDefs.concat([
            {
                targets: 'uid:name',
                orderable: true,
                render: function (data, type, row) {
                    console.log(row);
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <div class="size-40 size-child border b-r-6">
                                <img data-src="${ Main.mediaURL('{{ Media::url() }}', row.user_avatar) }" src="${ Main.text2img(row.user_fullname, '000') }" class="b-r-6 lazyload" onerror="this.src='${ Main.text2img(row.user_fullname, '000') }'">
                            </div>
                            <div class="text-start lh-1 text-truncate">
                                <div class="fw-5 text-gray-900 text-truncate">
                                    <div class="text-truncate">
                                        ${row.user_fullname}
                                    </div>
                                    <div class="text-truncate text-gray-500 fs-12">
                                        ${row.user_email}
                                    </div>
                                </div>
                            </div>
                        </div>`;
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
                            <ul class="dropdown-menu border-1 border-gray-300 w-150 max-w-150 min-w-150">
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
        DataTable.columns(['users.fullname:name', 'users.email:name', 'users.avatar:name']).visible(false);
    </script>
@endsection
