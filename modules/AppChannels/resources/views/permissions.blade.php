<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="appchannels" name="permissions[appchannels]" @checked( array_key_exists("appchannels", $permissions ) )>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="appchannels">
                {{ __("Channels") }}
            </label>
        </div>
        <input class="form-control d-none" name="labels[appchannels]" type="text" value="Channels">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <label for="max_channels" class="form-label">{{ __('Max channels') }}</label>
                    <div class="text-gray-600 fs-12 mb-2">{{ __("Enter the maximum number of channels allowed in this package. Set the value to -1 for unlimited channels.") }}</div>
                    <input class="form-control" name="permissions[max_channels]" id="max_channels" type="number" required value="{{ $permissions['max_channels'] ?? -1 }}">
                    <input class="form-control d-none" name="labels[max_channels]" type="text" value="Max channels">
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-4">
                    <label class="form-label">{{ __('The number of channels is calculated based on') }}</label>
                    <div class="d-flex gap-8 flex-column flex-lg-row flex-md-column">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="permissions[channel_calculate_by]" value="1" id="channel_calculate_by_1" @checked(($permissions['channel_calculate_by'] ?? 1) == 1)>
                            <label class="form-check-label mt-1" for="channel_calculate_by_1">
                                {{ __('Each social network') }}
                            </label>
                        </div>
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="permissions[channel_calculate_by]" value="2" id="channel_calculate_by_2" @checked(($permissions['channel_calculate_by'] ?? 1) == 2)>
                            <label class="form-check-label mt-1" for="channel_calculate_by_2">
                                {{ __('Entire social network') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 allow_channels">
                <div class="mb-0">
                    <div class="d-flex gap-8 justify-content-between border-bottom mb-3 pb-2">
                        <div class="fw-5 text-gray-800 fs-14 mb-2">{{ __('Allow channels') }}</div>
                        <div class="form-check">
                            <input class="form-check-input checkbox-all" data-checkbox-parent=".allow_channels" type="checkbox" value="" id="allow_channels">
                            <label class="form-check-label" for="allow_channels">
                                {{ __('Select All') }}
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        @foreach(app('channels') as $value)
                            @php
                                $key = 'appchannels.' . $value['key'];
                                $labelValue = old("labels.$key", $labels[$key] ?? $value['module_name']);
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
                                        {{ $value['module_name'] }}
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