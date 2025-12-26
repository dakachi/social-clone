@extends('layouts.app')

@section('form', json_encode([
    'method' => 'POST'
]))

@section('sub_header')
    <x-sub-header
        title="{{ __('Blog Categories') }}"
        description="{{ __('Organize blog categories seamlessly for efficient content management') }}"
        :count="$total"
    >
        <a class="btn btn-dark btn-sm actionItem d-flex align-items-center gap-1 text-nowrap"
           href="{{ module_url("update") }}"
           data-popup="BlogCategoriesModal">
            <i class="fa-light fa-plus"></i>
            <span>{{ __('Create new') }}</span>
        </a>

    </x-sub-header>
@endsection

@section('content')
    <div class="container pb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="table-info"></div>
            <div class="d-flex flex-wrap gap-8">
                <div class="d-flex">
                    <div class="form-control form-control-sm">
                        <span class="btn btn-icon">
                            <i class="fa-duotone fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input class="ajax-scroll-filter" name="keyword" placeholder="{{ __('Search') }}" type="text">
                        <button class="btn btn-icon">
                            <div class="form-check form-check-sm mb-0">
                                <input class="form-check-input checkbox-all" id="select_all" type="checkbox">
                            </div>
                        </button>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="btn-group position-static">
                        <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                            <i class="fa-light fa-filter"></i> {{ __("Filters") }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
                            <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                                <span><i class="fa-light fa-filter"></i></span>
                                <span>{{ __("Filters") }}</span>
                            </div>
                            <div class="p-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __("Status") }}</label>
                                        <select id="filterStatus" class="form-select form-select-sm datatable_filter" name="status">
                                            <option value="-1">{{ __('All') }}</option>
                                            <option value="1">{{ __("Enable") }}</option>
                                            <option value="0">{{ __("Disable") }}</option>
                                        </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn-group position-static">
                    <button class="btn btn-outline btn-primary btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" aria-expanded="true">
                        <i class="fa-light fa-grid-2"></i> {{ __("Actions") }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-1 border-gray-300 px-2 w-100 max-w-125" data-popper-placement="bottom-end">
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
                            <a class="dropdown-item p-2 rounded d-flex gap-8 fw-5 fs-14 actionMultiItem" href="{{ module_url("destroy") }}" data-call-success="Main.ajaxScroll(true);">
                                <span class="size-16 me-1 text-center"><i class="fa-light fa-trash-can-list"></i></span>
                                <span>{{ __("Delete") }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container px-4">
              <div class="ajax-scroll" data-url="{{ module_url("list") }}" data-resp=".channel-list" data-scroll="document">
                <div class="row channel-list"></div>
                <div class="pb-30 pt-30 ajax-scroll-loading d-none">
                    <div class="app-loading mx-auto mt-10 pl-0 pr-0">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        function reloadList() {
            let keyword = $('input[name="keyword"]').val();
            let status = $('#filterStatus').val();

            console.log("🔄 Reloading with status:", status, "keyword:", keyword);

            Main.ajaxScroll({
                scroll: true,
                url: "{{ module_url('list') }}",
                data: {
                    keyword: keyword,
                    status: status
                },
                target: ".channel-list"
            });
        }

        $('#filterStatus').on('change', function () {
            reloadList();
        });

        $('input[name="keyword"]').on('input', function () {
            reloadList();
        });
    });
</script>
@endsection


