<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <div class="form-check">
            <input class="form-check-input prevent-toggle" type="checkbox" value="1" id="apppublishing" name="permissions[apppublishing]" @checked( array_key_exists("apppublishing", $permissions ) )>
            <label class="fw-6 fs-14 text-gray-700 ms-2" for="apppublishing">
                {{ __("Publishing") }}
            </label>
        </div>
        <input class="form-control d-none" name="labels[apppublishing]" type="text" value="Publishing">
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <label for="apppublishing.max_post" class="form-label">{{ __('Maximum Posts per Month') }}</label>
                    <div class="text-gray-600 fs-12 mb-2">{{ __("Enter the total number of posts permitted for this package; input -1 for unlimited posts") }}</div>
                    <input class="form-control" name="permissions[apppublishing.max_post]" id="apppublishing.max_post" type="number" value="{{ $permissions['apppublishing.max_post'] ?? '100' }}">
                    <input class="form-control d-none" name="labels[apppublishing.max_post]" type="text" value="Maximum Posts per Month">
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-2">
                    <div class="d-flex gap-4 justify-content-between">
                        <div class="fw-5 text-gray-800 fs-14 mb-2">{{ __('Features') }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-8">
                        <div class="mb-2">
                            <div class="form-check me-3">
                                <input class="form-check-input checkbox-item" type="checkbox" name="permissions[apppublishingcampaigns]" value="1" id="apppublishingcampaigns" @checked( array_key_exists("apppublishingcampaigns", $permissions ) )>
                                <label class="form-check-label mt-1 text-truncate" for="apppublishingcampaigns">
                                    {{ __("Campaign Publishing") }}
                                </label>
                            </div>
                            <input class="form-control d-none" name="labels[apppublishingcampaigns]" type="text" value="Campaign Publishing">
                        </div>
                        <div class="mb-2">
                            <div class="form-check me-3">
                                <input class="form-check-input checkbox-item" type="checkbox" name="permissions[apppublishinglabels]" value="1" id="apppublishinglabels" @checked( array_key_exists("apppublishinglabels", $permissions ) )>
                                <label class="form-check-label mt-1 text-truncate" for="apppublishinglabels">
                                    {{ __("Label Publishing") }}
                                </label>
                            </div>
                            <input class="form-control d-none" name="labels[apppublishinglabels]" type="text" value="Label Publishing">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 allow_publishing">
                <div class="mb-0">
                    <div class="d-flex gap-8 justify-content-between border-bottom mb-3 pb-2">
                        <div class="fw-5 text-gray-800 fs-14 mb-2">{{ __('Allow post to channels') }}</div>
                        <div class="form-check">
                            <input class="form-check-input checkbox-all" data-checkbox-parent=".allow_publishing" type="checkbox" value="" id="allow_publishing">
                            <label class="form-check-label" for="allow_publishing">
                                {{ __('Select All') }}
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        @foreach(app('channels') as $value)
                            @php
                                $key = 'apppublishing.' . $value['key'];
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
