<script type="text/javascript">
    var columnDefs = [
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
        @if((isset($column_status) && $column_status) || !isset($column_status) )
            @if(isset($Datatable['status_filter']))
            {
                targets: 'status:name',
                orderable: true,
                className: 'min-w-80',
                render: function (data, type, row) {
                    switch(data) {
                        @foreach($Datatable['status_filter'] as $value)
                            case {{ $value['value'] }}:
                            var status_class = "badge-{{ $value['color'] ?? 'light' }}";
                            var status_text = "{{ $value['label'] }}";
                            var status_icon = "{{ $value['icon'] ?? '' }}";
                            break;
                        @endforeach
                    }

                    return `
                        <div class="btn-group pointer">
                            <span class="badge badge-outline badge-sm ${status_class} dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown"><i class="${status_icon} pe-2"></i> ${status_text}</span>
                            <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-auto max-w-210">
                                @foreach($Datatable['status_filter'] as $value)
                                    @if($value['value'] != -1)
                                    <li>
                                        <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("status/".($value['name'] ?? $value['value'] )) }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                            <span class="size-16 me-1 text-center"><i class="{{ $value['icon'] ?? '' }}"></i></span>
                                            <span class="text-truncate">{{ $value['label'] }}</span>
                                        </a>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>`;
                }
            },
            @endif
        @endif
        @if((isset($column_actions) && $column_actions) || !isset($column_actions) )
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
                                @php
                                    if (!isset($edit_url) || $edit_url){
                                        $edit_url = module_url("update");
                                    }
                                @endphp

                                @if(isset($edit_popup) && $edit_popup)
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 {{ ( $edit_popup ?? '' ) ? 'actionItem' : '' }}" href="{{ $edit_url }}" data-id="${row.id_secure}" data-popup="{{ $edit_popup ?? '' }}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                        <span>{{ $edit_text ?? __("Edit") }}</span>
                                    </a>
                                @else
                                    <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6" href="{{ module_url("edit") }}/${row.id_secure}">
                                        <span class="size-16 me-1 text-center"><i class="fa-light fa-pen-to-square"></i></span>
                                        <span>{{ $edit_text ?? __("Edit") }}</span>
                                    </a>
                                @endif
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @if(!isset($delete) || $delete)
                            <li class="mx-2">
                                <a class="dropdown-item d-flex gap-8 fw-5 fs-14 b-r-6 actionItem" data-id="${row.id_secure}" href="{{ module_url("destroy") }}" data-confirm="{{ __("Are you sure you want to delete this item?") }}" data-call-success="Main.DataTable_Reload('#{{ $Datatable['element'] }}')">
                                    <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                    <span>{{ __("Delete") }}</span>
                                </a>
                            </li>
                            @endif        
                        </ul>
                    </div>
                `;
            },
        },
        @endif
    ];
</script>