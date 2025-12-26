@extends('layouts.app')

@section('sub_header')
    <x-sub-header 
        title="{{ __('Affiliate Commissions') }}" 
        description="{{ __('Approved Affiliate Commissions: Verified earnings from referrals and partnerships') }}" 
        :count="$total"
    >
    </x-sub-header>
@endsection

@section('content')
    @component('components.datatable', [ "Datatable" => $Datatable ]) @endcomponent
@endsection

@section('script')
    <script type="text/javascript">
        var DataTable = Main.DataTable("#{{ $Datatable['element'] }}", {
            
            @if(!empty($Datatable['columns']))
                "columns": {!! json_encode($Datatable['columns']) !!},
            @endif

            @if(!empty($Datatable['lengthMenu']))
                "lengthMenu": {!! json_encode($Datatable['lengthMenu']) !!},
            @endif

            @if(!empty($Datatable['order']))
                "order": {!! json_encode($Datatable['order']) !!},
            @endif

            "columnDefs": [
                {
                    targets: 'id_secure:name',
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input checkbox-item" name="id[]" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
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
                                            <a class="text-gray-800 text-hover-primary actionItem" data-id="${row.RecordID}" href="{{ module_url("update") }}" data-popup="AdminPaymentHistoryModal">
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
                {
                    targets: 'payment_history.status:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        switch(data) {
                          case 0:
                            var status_class = "badge-danger";
                            var status_text = "{{ __("Refund") }}";
                            var status_icon = "fal fa-sync-alt";
                            break;                                                     
                          default:
                            var status_class = "badge-success";
                            var status_text = "{{ __("Success") }}";
                            var status_icon = "fal fa-check-circle";
                        }

                        return `
                            <div class="btn-group">
                                <span class="badge badge-outline badge-sm pointer ${status_class}"><i class="${status_icon} pe-2"></i> ${status_text}</span>
                        
                            </div>`;
                    }
                },                
                {
                    targets: 'affiliate.status:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        switch(data) {
                          case 1:
                            var status_class = "badge-success";
                            var status_text = "{{ __("Approved") }}";
                            var status_icon = "fas fa-check-circle";
                            break;
                          case 2:
                            var status_class = "badge-danger";
                            var status_text = "{{ __("Reject") }}";
                            var status_icon = "fas fa-times-circle";
                            break;                                                        
                          default:
                            var status_class = "badge-warning";
                            var status_text = "{{ __("Pending") }}";
                            var status_icon = "fas fa-hourglass-half";
                        }

                        return `
                            <div class="btn-group">
                                <span class="badge badge-outline badge-sm ${status_class} dropdown-toggle dropdown-arrow-hide pointer" data-bs-toggle="dropdown"><i class="${status_icon} pe-2"></i> ${status_text}</span>
                                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("status/pending") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye-slash"></i></span>
                                            <span>{{ __("Pending") }}</span>
                                        </a>
                                    </li>                                
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("status/approve") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                            <span >{{ __("Approve") }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("status/reject") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                            <span >{{ __("Reject") }}</span>
                                        </a>
                                    </li>                                    
                                </ul>
                            </div>`;
                    }
                },


            ]
        });

        DataTable.columns(['users.fullname:name', 'users.email:name', 'users.avatar:name']).visible(false);        
    </script>

@endsection
