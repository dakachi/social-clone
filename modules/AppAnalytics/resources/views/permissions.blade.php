<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="appanalytics" name="permissions[appanalytics]" @checked( array_key_exists("appanalytics", $permissions ) )>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="appanalytics">
                {{ __("Analytics") }}
            </label>
        </div>
        <input class="form-control d-none" name="labels[appanalytics]" type="text" value="Channels">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 allow_analytics">
                <div class="mb-0">
                    <div class="d-flex gap-8 justify-content-between border-bottom mb-3 pb-2">
                        <div class="fw-5 text-gray-800 fs-14 mb-2">{{ __('Allow channels') }}</div>
                        <div class="form-check">
                            <input class="form-check-input checkbox-all" data-checkbox-parent=".allow_analytics" type="checkbox" value="" id="allow_analytics">
                            <label class="form-check-label" for="allow_analytics">
                                {{ __('Select All') }}
                            </label>
                        </div>
                    </div>
                    <div class="row">

                        @php
                        $analytics = Analytics::getAnalytics();
                        @endphp

                        @foreach($analytics as $k => $value)
                            @php
                                $key = 'appanalytics.' . $k;
                                $labelValue = old("labels.$key", $labels[$key] ?? $key);
                            @endphp

                            <div class="col-md-4 mb-3">
                                <div class="form-check mb-1">
                                    <input class="form-check-input checkbox-item" 
                                           type="checkbox" 
                                           name="permissions[{{ $key }}]" 
                                           value="1" 
                                           id="{{ $key }}" 
                                           @checked(array_key_exists($key, $permissions))>
                                    <label class="form-check-label mt-1 text-truncate" for="{{ $key }}">
                                        {{ str_replace( "_", " ", ucfirst($k)) }}
                                    </label>
                                </div>
                                <input class="form-control form-control-sm d-none" 
                                       type="text" 
                                       name="labels[{{ $key }}]" 
                                       value="{{ $labelValue }}" 
                                       placeholder="{{ __('Custom label') }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>