@extends('layouts.app')


@section('sub_header')
    <x-sub-header 
        title="{{ __('Affiliate Withdrawal Requests') }}" 
        description="{{ __('Manage and process withdrawal requests efficiently') }}" 
        :count="$total"
    >
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    @component('components.datatable_script', [ "Datatable" => $Datatable, "edit_popup" => "addAffiliateWithdrawalModal" , "edit_text" => __("Edit note") , "edit_url" => "addAffiliateWithdrawalModal" , "column_actions" => true, "column_status" => true]) @endcomponent
    <script type="text/javascript">
        columnDefs = columnDefs.concat([
            {
                targets: 'affiliate_uid:name',
                orderable: true,
                render: function (data, type, row) {
                    return `
                        <div class="d-flex gap-8 align-items-center">
                            <div class="size-40 size-child border b-r-6">
                                <img data-src="${ Main.mediaURL('{{ Media::url() }}', row.user_avatar) }" src="${ Main.text2img(row.user_fullname, '000') }" class="b-r-6 lazyload" onerror="this.src='${ Main.text2img(row.user_fullname, '000') }'">
                            </div>
                            <div class="text-start lh-1 text-truncate">
                                <div class="fw-5 text-gray-900 text-truncate">
                                    <div class="text-truncate">
                                        <a class="text-gray-800 text-hover-primary actionItem" data-id="${row.RecordID}" href="{{ module_url("update") }}" data-popup="addAffiliateWithdrawalModal">
                                            ${row.user_fullname}
                                        </a>
                                    </div>
                                    <div class="text-truncate text-gray-500 fs-12">
                                        ${row.user_email}
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
        DataTable.columns(['users.fullname:name', 'users.email:name', 'users.avatar:name']).visible(false);
    </script>
@endsection