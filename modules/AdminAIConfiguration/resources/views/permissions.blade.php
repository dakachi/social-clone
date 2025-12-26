<div class="card b-r-6 border-gray-300 mb-3">
    <div class="card-header">
        <label class="fw-6 fs-14 text-gray-700">
            {{ __("AI Credits") }}
        </label>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-4">
                    <label for="ai_word_credits" class="form-label">{{ __('Word Credits') }}</label>
                    <div class="text-gray-600 fs-12 mb-2">{{ __("Specify the maximum number of credits permitted for this package. To allow unlimited credits, enter -1") }}</div>
                    <input class="form-control" name="permissions[ai_word_credits]" id="ai_word_credits" type="number" value="{{ $permissions['ai_word_credits'] ?? '1000' }}">
                </div>
            </div>
            <div class="d-none">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="ai_media_credits" class="form-label">{{ __('Media Credits') }}</label>
                        <div class="text-gray-600 fs-12 mb-2">{{ __("Specify the maximum number of credits permitted for this package. To allow unlimited credits, enter -1") }}</div>
                        <input class="form-control" name="permissions[ai_media_credits]" id="ai_media_credits" type="number" value="{{ $permissions['ai_media_credits'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="ai_character_included" class="form-label">{{ __('Characters Included') }}</label>
                        <div class="text-gray-600 fs-12 mb-2">{{ __("Specify the maximum number of credits permitted for this package. To allow unlimited credits, enter -1") }}</div>
                        <input class="form-control" name="permissions[ai_character_included]" id="ai_character_included" type="number" value="{{ $permissions['ai_character_included'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="ai_minutes_included" class="form-label">{{ __('Minutes Included') }}</label>
                        <div class="text-gray-600 fs-12 mb-2">{{ __("Specify the maximum number of credits permitted for this package. To allow unlimited credits, enter -1") }}</div>
                        <input class="form-control" name="permissions[ai_minutes_included]" id="ai_minutes_included" type="number" value="{{ $permissions['ai_minutes_included'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
