@extends('layouts.app')
@include('apppublishing::header_center', [])

@section('content')
    <div class="container">

        <div class="mt-4 mb-4">
            <div class="d-flex flex-column flex-lg-row flex-md-column align-items-md-start align-items-lg-center justify-content-between">
                <div class="my-3 d-flex flex-column gap-8">
                    <h1 class="fs-20 font-medium lh-1 text-gray-900">
                        <span>{{ __("Publishing Labels") }}</span> <span class="fs-14 text-gray-700">(<span class="text-primary">{{ Number::format($total) }}</span>{{ __(" records") }} )</span>
                    </h1>
                    <div class="d-flex align-items-center gap-20 fw-5 fs-14">
                        <div class="d-flex gap-8">
                            <span class="text-gray-600"><span class="text-gray-600">{{ __('Publish labels to ensure clear and effective communication.') }}</span></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-8">
                    <a class="btn btn-dark btn-sm" href="{{ module_url("create") }}">
                        <span><i class="fa-light fa-plus"></i></span>
                        <span>{{ __('Add new') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <form class="actionMulti">
            <div class="card mt-5">
                <div class="card-header">
                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-8">
                        <div class="table-info"></div>
                        <div class="d-flex flex-wrap gap-8">
                            <div class="d-flex">
                                <div class="form-control form-control-sm">
                                    <button class="btn btn-icon">
                                        <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                                    </button>
                                    <input name="datatable_filter[search]" placeholder="{{ __('Search') }}" type="text"/>
                                </div>
                            </div>
                            <div class="d-flex">
                                <select class="form-select form-select-sm datatable_filter" name="datatable_filter[status]">
                                    <option value="-1">{{ __('All') }}</option>
                                    <option value="1">{{ __('Enable') }}</option>
                                    <option value="0">{{ __('Disable') }}</option>
                                </select>
                            </div>
                            <div class="d-flex">
                                <div class="btn-group">
                                    <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown">
                                        <i class="fa-light fa-grid-2"></i> {{ __('Actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                                        <li>
                                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/enable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                                <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                                <span >{{ __('Enable') }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("status/disable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                                <span class="size-16 me-1 text-center"><i class="fa-light fa-eye-slash"></i></span>
                                                <span>{{ __('Disable') }}</span>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                                <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                                <span>{{ __('Delete') }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 border-0">
                    @if(!empty($Datatable['columns']))
                    <div class="table-responsive">
                        <table id="{{ $Datatable['element'] }}" data-url="{{ module_url( "list") }}" class="display table table-bordered table-hide-footer w-100">
                            <thead>
                                <tr>
                                    @foreach($Datatable['columns'] as $key => $column)

                                        @if($key == 0)
                                            <th class="align-middle w-10px pe-2">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input checkbox-all" type="checkbox" data-checkbox-parent=".table-responsive"/>
                                                </div>
                                            </th>
                                        @elseif($key + 1 == count($Datatable['columns']))
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
                    </div>
                    @endif
                </div>
                <div class="card-footer justify-center border-top-0">
                    <div class="d-flex flex-wrap justify-content-center align-items-center w-100 justify-content-md-between gap-20">
                        <div class="d-flex align-items-center gap-8 fs-14 text-gray-700 table-size"></div>
                        <div class="d-flex table-pagination"></div>
                    </div>
                </div>
            </div>
        </form>

    </div>
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
                    targets: 'name:name',
                    orderable: true,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex gap-8 align-items-center">
                                <div class="size-10 bg-${row.Color}-400 border b-r-6"></div>
                                <div class="text-start lh-1.1">
                                    <div class="fw-5 text-gray-900">
                                        <a href="{{ module_url( "edit") }}/${row.RecordID}" class="text-gray-800 text-hover-primary">
                                            ${row.Name}
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 'succeed:name',
                    orderable: false,
                },
                {
                    targets: 'failed:name',
                    orderable: false,
                },
                {
                    targets: 'status:name',
                    orderable: true,
                    className: 'min-w-80',
                    render: function (data, type, row) {
                        switch(data) {
                          case 1:
                            var status_class = "badge-success";
                            var status_text = "{{ __("Enable") }}";
                            var status_icon = "fa-light fa-eye";
                            break;
                          default:
                            var status_class = "badge-light";
                            var status_text = "{{ __("Disable") }}";
                            var status_icon = "fa-light fa-eye-slash";
                        }

                        return `
                            <div class="btn-group">
                                <span class="badge badge-outline badge-sm ${status_class} dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown"><i class="${status_icon} pe-2"></i> ${status_text}</span>
                                <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125">
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("status/enable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye"></i></span>
                                            <span >{{ __("Enable") }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item p-1 rounded d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("status/disable") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-eye-slash"></i></span>
                                            <span>{{ __("Disable") }}</span>
                                        </a>
                                    </li>
                                </ul>
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
                                    {{ __('Actions') }}</button>
                                <ul class="dropdown-menu border-1 border-gray-300 px-2 w-80 max-w-125">
                                    <li>
                                        <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" data-id="" href="{{ module_url("edit/") }}/${row.RecordID}">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                            <span>{{ __('Edit') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.RecordID}" href="{{ module_url("destroy") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                            <span>{{ __('Delete') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        `;
                    },
                },
            ]
        });

        DataTable.columns(['color:name']).visible(false);
    </script>
@endsection
