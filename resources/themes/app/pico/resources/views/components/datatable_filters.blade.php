@if(!empty($filters))
<div class="d-flex">
    <div class="btn-group position-static">
        <button class="btn btn-outline btn-light btn-sm dropdown-toggle dropdown-arrow-hide" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
            <i class="fa-light fa-filter"></i> {{ __("Filters") }}
        </button>
        <div class="dropdown-menu dropdown-menu-end border-1 border-gray-300 w-full max-w-250" data-popper-placement="bottom-end">
            <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2 fw-6 fs-16 gap-8">
                <div>
                    <span><i class="fa-light fa-filter"></i></span>
                    <span>{{ __("Filters") }}</span>
                </div>
                <a href="javascript:void(0);"  data-bs-dropdown-close="true">
                    <i class="fal fa-times"></i>
                </a>
            </div>
            <div class="p-3">
                @foreach($filters as $filter)
                    <div class="mb-3">
                        <label class="form-label">{{ $filter['label'] }}</label>
                        <select class="form-select form-select-sm datatable_filter" name="{{ $filter['name'] }}">
                            @foreach($filter['options'] as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif